<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupRestoreController extends Controller
{
    /**
     * Show backup & restore dashboard index.
     */
    public function index()
    {
        return view('admin.backup.index');
    }

    /**
     * Generate backup, dump MySQL DB, package files, and download as ZIP.
     */
    public function backup()
    {
        try {
            // Set execution limit to 5 minutes to prevent timeout
            set_time_limit(300);

            $timestamp = date('Ymd_His');
            $backupDirName = 'ipcall_backup_' . $timestamp;
            $tempPath = storage_path('app/temp_' . $backupDirName);

            if (!File::exists($tempPath)) {
                File::makeDirectory($tempPath, 0777, true, true);
            }

            // 1. Create a metadata marker file to identify this as a valid backup
            $metadata = [
                'app_name' => 'ip-call',
                'backup_type' => 'full',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '2.0',
            ];
            File::put($tempPath . '/backup_metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));

            // 2. Dump the MySQL Database
            $dbConfig = config('database.connections.mysql');
            $host = $dbConfig['host'];
            $port = $dbConfig['port'] ?? '3306';
            $database = $dbConfig['database'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];

            $sqlFile = $tempPath . '/db_dump.sql';
            
            $escapedHost = escapeshellarg($host);
            $escapedPort = escapeshellarg($port);
            $escapedUsername = escapeshellarg($username);
            $escapedDatabase = escapeshellarg($database);
            $escapedSqlFile = escapeshellarg($sqlFile);

            $passwordOpt = $password !== '' ? '--password=' . escapeshellarg($password) : '';
            $command = "mysqldump -h {$escapedHost} -P {$escapedPort} -u {$escapedUsername} {$passwordOpt} {$escapedDatabase} > {$escapedSqlFile}";

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception("Database dump failed with code {$returnVar}. Command output: " . implode("\n", $output));
            }

            // 3. Copy public/uploads
            $uploadsSource = public_path('uploads');
            if (File::exists($uploadsSource) && File::isDirectory($uploadsSource)) {
                File::copyDirectory($uploadsSource, $tempPath . '/uploads');
            }

            // 4. Copy records folder (which can be /var/www/html/records)
            $recordsSource = '/var/www/html/records';
            if (!File::exists($recordsSource) || !File::isDirectory($recordsSource)) {
                $recordsSource = public_path('records');
            }
            if (File::exists($recordsSource) && File::isDirectory($recordsSource)) {
                File::copyDirectory($recordsSource, $tempPath . '/records');
            }

            // 5. Zip everything
            $zipFile = storage_path('app/' . $backupDirName . '.zip');
            $escapedZipFile = escapeshellarg($zipFile);
            $escapedTempPath = escapeshellarg($tempPath);

            $zipCommand = "cd {$escapedTempPath} && zip -r {$escapedZipFile} .";
            exec($zipCommand, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception("Failed to package ZIP archive. Error code: {$returnVar}");
            }

            // 6. Clean up temporary directory
            File::deleteDirectory($tempPath);

            // 7. Download and delete after sending
            return response()->download($zipFile)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error("Backup failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore database and directories from uploaded backup ZIP.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip',
        ]);

        try {
            set_time_limit(300);

            $file = $request->file('backup_file');
            
            // Create a temporary restore folder
            $restoreDirName = 'restore_' . date('Ymd_His') . '_' . uniqid();
            $tempRestorePath = storage_path('app/' . $restoreDirName);
            File::makeDirectory($tempRestorePath, 0777, true, true);

            // Save the uploaded file to temp path
            $zipPath = $file->storeAs('temp', $restoreDirName . '.zip');
            $fullZipPath = storage_path('app/' . $zipPath);

            // Extract the ZIP file
            $escapedZipPath = escapeshellarg($fullZipPath);
            $escapedRestorePath = escapeshellarg($tempRestorePath);

            $output = [];
            $returnVar = 0;
            $unzipCommand = "unzip -o {$escapedZipPath} -d {$escapedRestorePath}";
            exec($unzipCommand, $output, $returnVar);

            // Delete uploaded ZIP file as we've extracted it
            if (File::exists($fullZipPath)) {
                File::delete($fullZipPath);
            }

            if ($returnVar !== 0) {
                File::deleteDirectory($tempRestorePath);
                throw new \Exception("Failed to extract ZIP archive. Error code: {$returnVar}");
            }

            // 1. Verify marker/metadata file to confirm this is a valid backup file
            $metadataFile = $tempRestorePath . '/backup_metadata.json';
            if (!File::exists($metadataFile)) {
                File::deleteDirectory($tempRestorePath);
                return redirect()->back()->with('error', 'Unggah berkas gagal: Penanda backup tidak ditemukan (backup_metadata.json). Harap unggah file backup yang valid.');
            }

            $metadata = json_decode(File::get($metadataFile), true);
            if (!$metadata || !isset($metadata['app_name']) || $metadata['app_name'] !== 'ip-call') {
                File::deleteDirectory($tempRestorePath);
                return redirect()->back()->with('error', 'Unggah berkas gagal: File zip bukan merupakan backup dari aplikasi IP-Call.');
            }

            // 2. Database Restore
            $sqlFile = $tempRestorePath . '/db_dump.sql';
            if (File::exists($sqlFile)) {
                $dbConfig = config('database.connections.mysql');
                $host = $dbConfig['host'];
                $port = $dbConfig['port'] ?? '3306';
                $database = $dbConfig['database'];
                $username = $dbConfig['username'];
                $password = $dbConfig['password'];

                $escapedHost = escapeshellarg($host);
                $escapedPort = escapeshellarg($port);
                $escapedUsername = escapeshellarg($username);
                $escapedSqlFile = escapeshellarg($sqlFile);
                $escapedDatabase = escapeshellarg($database);

                $passwordOpt = $password !== '' ? '--password=' . escapeshellarg($password) : '';
                
                // Perform DB restoration
                $mysqlCommand = "mysql -h {$escapedHost} -P {$escapedPort} -u {$escapedUsername} {$passwordOpt} {$escapedDatabase} < {$escapedSqlFile}";
                exec($mysqlCommand, $output, $returnVar);

                if ($returnVar !== 0) {
                    File::deleteDirectory($tempRestorePath);
                    throw new \Exception("Database restore failed with code {$returnVar}. Command output: " . implode("\n", $output));
                }
            }

            // 3. Restore folders safely (uploads & records)
            // Backup paths
            $uploadsDest = public_path('uploads');
            $recordsDest = '/var/www/html/records';
            if (!File::exists($recordsDest) || !File::isDirectory($recordsDest)) {
                $recordsDest = public_path('records');
            }

            // Safe swap for uploads folder
            $restoredUploads = $tempRestorePath . '/uploads';
            if (File::exists($restoredUploads) && File::isDirectory($restoredUploads)) {
                $this->safeSwapFolder($restoredUploads, $uploadsDest);
            }

            // Safe swap for records folder
            $restoredRecords = $tempRestorePath . '/records';
            if (File::exists($restoredRecords) && File::isDirectory($restoredRecords)) {
                $this->safeSwapFolder($restoredRecords, $recordsDest);
            }

            // Clean up extraction folder
            File::deleteDirectory($tempRestorePath);

            return redirect()->back()->with('success', 'Data dan berkas berhasil dipulihkan (restore) sepenuhnya!');

        } catch (\Exception $e) {
            Log::error("Restore failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Pemulihan data gagal: ' . $e->getMessage());
        }
    }

    /**
     * Safely swap a folder by renaming the current destination, copying the new source,
     * and deleting the backup rename folder if copy is successful. Rollback on failure.
     */
    private function safeSwapFolder($src, $dest)
    {
        $backupDest = $dest . '_old_' . uniqid();

        // 1. Rename existing folder to temporary backup name
        if (File::exists($dest)) {
            File::move($dest, $backupDest);
        }

        try {
            // 2. Copy the new folder to destination
            File::makeDirectory($dest, 0777, true, true);
            File::copyDirectory($src, $dest);

            // 3. Clean up the temporary backup folder
            if (File::exists($backupDest)) {
                File::deleteDirectory($backupDest);
            }
        } catch (\Exception $e) {
            // Rollback: delete the failed destination, restore the backup
            if (File::exists($dest)) {
                File::deleteDirectory($dest);
            }
            if (File::exists($backupDest)) {
                File::move($backupDest, $dest);
            }
            throw $e;
        }
    }
}
