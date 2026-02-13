import psutil
import tkinter as tk
from tkinter import messagebox
from tkinter import ttk
from tkcalendar import DateEntry
from PIL import Image, ImageTk
import signal
import sys
import datetime
import requests
import os
import subprocess
import argparse
import threading
import time
import requests
import os
import subprocess
import argparse

# Default background file path
DEFAULT_BG_FILE = "/home/nursecallserver/ip-call/python/assets/bg.JPEG"

class USBMonitor:
    def __init__(self, check_interval=1, bg_file=DEFAULT_BG_FILE):
        self.check_interval = check_interval
        self.bg_file = bg_file
        self.known_devices = set()
        self.root = None
        self.usb_window = None

    def detect_usb(self):
        usb_drives = []
        partitions = psutil.disk_partitions()
        for partition in partitions:
            if partition.mountpoint.startswith('/media') or partition.mountpoint.startswith('/mnt'):
                drive_info = {
                    'device': partition.device,
                    'mountpoint': partition.mountpoint,
                    'fstype': partition.fstype,
                    'opts': partition.opts
                }
                usb_drives.append(drive_info)
        return usb_drives

    def show_usb_window(self):
        if self.usb_window is None:
            self.usb_window = tk.Toplevel(self.root)
            self.usb_window.title("USB Drive Detected")
            # Increased window size as requested
            window_width = 800
            window_height = 480
            self.usb_window.geometry(f"{window_width}x{window_height}")
            self.center_window(self.usb_window)
            
            # Use default theme to avoid Xlib errors with 'clam' on some systems
            # style = ttk.Style()
            # style.theme_use('clam')
            
            # Background Setup
            self.canvas = tk.Canvas(self.usb_window, width=window_width, height=window_height, bg='black')
            self.canvas.pack(fill='both', expand=True)
            
            try:
                # 1. Try relative URL first (best for portability)
                script_dir = os.path.dirname(os.path.abspath(__file__))
                bg_path_1 = os.path.join(script_dir, "assets", "bg.JPEG")
                
                # 2. Try hardcoded path (fallback for specific deployment)
                bg_path_2 = "/home/nursecallserver/ip-call/python/assets/bg.JPEG"
                
                # 3. Use the arg-provided path
                bg_path_3 = self.bg_file

                final_bg_path = None
                
                if os.path.exists(bg_path_1):
                    final_bg_path = bg_path_1
                elif os.path.exists(bg_path_2):
                    final_bg_path = bg_path_2
                elif os.path.exists(bg_path_3):
                    final_bg_path = bg_path_3
                
                if final_bg_path:
                    print(f"Loading background from: {final_bg_path}")
                    self.background_image = Image.open(final_bg_path)
                    # Compatibility fix for older Pillow versions
                    if hasattr(Image, 'Resampling'):
                        resample_method = Image.Resampling.LANCZOS
                    else:
                        resample_method = Image.LANCZOS
                        
                    self.background_image = self.background_image.resize((window_width, window_height), resample_method)
                    
                    if self.background_image.mode != 'RGB':
                        self.background_image = self.background_image.convert('RGB')
                    
                    self.background_photo = ImageTk.PhotoImage(self.background_image)
                    self.canvas.create_image(0, 0, image=self.background_photo, anchor='nw')
                else:
                    error_msg = f"Background image not found.\nTried:\n1. {bg_path_1}\n2. {bg_path_2}\n3. {bg_path_3}"
                    print(error_msg)
                    messagebox.showerror("Error", error_msg)
                    self.canvas.configure(bg="#2c3e50") # Fallback nice dark blue-grey

            except Exception as e:
                print(f"Error loading background image: {e}")
                messagebox.showerror("Error", f"Failed to load background: {str(e)}")
                self.canvas.configure(bg="#2c3e50")

            # Main Card Container
            card_frame = tk.Frame(self.usb_window, bg="white", padx=25, pady=25, relief="raised", borderwidth=1)
            card_frame.place(relx=0.5, rely=0.5, anchor="center")

            # Header - Use standard font family "Arial" or system default to prevent X11 BadLength
            header_label = tk.Label(card_frame, text="Ekspor Data", font=("Arial", 14, "bold"), bg="white", fg="#333")
            header_label.pack(pady=(0, 15))

            # Input Form
            form_frame = tk.Frame(card_frame, bg="white")
            form_frame.pack(fill="x", pady=5)

            today = datetime.date.today()

            # Date Inputs
            tk.Label(form_frame, text="Dari Tanggal:", bg="white", font=("Arial", 10)).grid(row=0, column=0, sticky="w", pady=5)
            # Removed extra styling from DateEntry
            self.start_date_entry = DateEntry(form_frame, maxdate=today, date_pattern='yyyy-mm-dd', width=12)
            self.start_date_entry.grid(row=0, column=1, padx=(10, 0), pady=5)

            tk.Label(form_frame, text="Sampai Tanggal:", bg="white", font=("Arial", 10)).grid(row=1, column=0, sticky="w", pady=5)
            # Removed extra styling from DateEntry
            self.end_date_entry = DateEntry(form_frame, maxdate=today, date_pattern='yyyy-mm-dd', width=12)
            self.end_date_entry.grid(row=1, column=1, padx=(10, 0), pady=5)

            # Audio Checkbox
            self.download_audio_var = tk.BooleanVar()
            audio_check = tk.Checkbutton(form_frame, text="Unduh Rekaman Telpon", variable=self.download_audio_var, 
                                       bg="white", font=("Arial", 10), activebackground="white")
            # Centered checkbox by removing sticky="w" and relying on default center anchor in column
            audio_check.grid(row=2, column=0, columnspan=2, pady=(15, 0))

            # Action Buttons
            btn_frame = tk.Frame(card_frame, bg="white")
            btn_frame.pack(pady=(20, 5), fill="x")

            # Custom Button Style helper
            def create_modern_button(parent, text, command, bg_color):
                btn = tk.Button(parent, text=text, command=command, bg=bg_color, fg="white", 
                              font=("Arial", 10, "bold"), relief="flat", activebackground=bg_color, 
                              padx=15, pady=6, cursor="hand2")
                # Hover effect (simple)
                def on_enter(e): btn['bg'] =  "#2980b9" if bg_color == "#3498db" else "#27ae60"
                def on_leave(e): btn['bg'] = bg_color
                btn.bind("<Enter>", on_enter)
                btn.bind("<Leave>", on_leave)
                return btn

            download_btn = create_modern_button(btn_frame, "Download", self.download, "#3498db")
            download_btn.pack(side="left", expand=True, fill="x", padx=(0, 5))

            backup_btn = create_modern_button(btn_frame, "Backup", self.backup, "#2ecc71")
            backup_btn.pack(side="right", expand=True, fill="x", padx=(5, 0))

    def hide_usb_window(self):
        if self.usb_window is not None:
            self.usb_window.destroy()
            self.usb_window = None

    def download(self):
        start_date = self.start_date_entry.get()
        end_date = self.end_date_entry.get()
        
        # Check USB first
        usb_drives = self.detect_usb()
        if not usb_drives:
             messagebox.showerror("Error", "USB Tidak Terdeteksi!")
             return
        
        usb_drive_path = usb_drives[0]['mountpoint']
        folder_name = f"DOWNLOAD_{datetime.datetime.now().strftime('%Y-%m-%d_%H.%M.%S')}"
        folder_path = os.path.join(usb_drive_path, folder_name)
        
        try:
            os.makedirs(folder_path, exist_ok=True)
        except Exception as e:
            messagebox.showerror("Error", f"Gagal membuat folder: {e}")
            return

        # Prepare list of files
        base_url = "http://localhost/ip-call/server"
        files_to_download = [
            (f"{base_url}/history/pdf.php?start_date={start_date}&end_date={end_date}", f"PDF_RIWAYAT_TELEPON_{start_date}~{end_date}.pdf"),
            (f"{base_url}/history/excel.php?start_date={start_date}&end_date={end_date}", f"EXCEL_RIWAYAT_TELEPON_{start_date}~{end_date}.xlsx"),
            (f"{base_url}/log/pdf.php?start_date={start_date}&end_date={end_date}", f"PDF_LOG_{start_date}~{end_date}.pdf"),
            (f"{base_url}/log/excel.php?start_date={start_date}&end_date={end_date}", f"EXCEL_LOG_{start_date}~{end_date}.xlsx"),
        ]

        # Check audio requirement
        download_audio = self.download_audio_var.get()
        audio_files = []
        if download_audio:
            try:
                list_url = f"http://localhost/ip-call/server/history/list_audio.php?start_date={start_date}&end_date={end_date}"
                response = requests.get(list_url)
                if response.status_code == 200:
                    audio_files = response.json()
            except Exception as e:
                print(f"Failed to fetch audio list: {e}")
        
        total_items = len(files_to_download) + len(audio_files)

        # Show Loading Popup
        progress_win = tk.Toplevel(self.root)
        progress_win.title("Mengunduh Data")
        progress_win.geometry("300x100")
        self.center_window(progress_win)
        progress_win.grab_set() 
        
        status_label = tk.Label(progress_win, text="Memulai download...", font=("Arial", 9))
        status_label.pack(pady=(10, 2))
        
        progress_var = tk.DoubleVar()
        progress_bar = ttk.Progressbar(progress_win, variable=progress_var, maximum=100, length=260)
        progress_bar.pack(pady=3)
        
        percent_label = tk.Label(progress_win, text="0%", font=("Arial", 8))
        percent_label.pack(pady=0)

        # UI Update Helpers
        def update_ui(prog, text):
            progress_var.set(prog)
            percent_label.config(text=f"{int(prog)}%")
            status_label.config(text=text)

        def finish_ui(success_count, fail_count):
            progress_win.destroy()
            msg = f"Download Selesai.\nBerhasil: {success_count}\nGagal: {fail_count}"
            if success_count > 0:
                messagebox.showinfo("Sukses", msg)
                self.hide_usb_window()
            else:
                messagebox.showwarning("Peringatan", msg)

        # Threaded Process
        def run_all_downloads():
            success = 0
            fail = 0
            processed = 0 

            # 1. Download Standard Reports
            for url, filename in files_to_download:
                processed += 1
                try:
                    # Update status
                    pct = (processed / total_items) * 100
                    self.root.after(0, update_ui, pct, f"Mengunduh {filename}...")
                    
                    response = requests.get(url)
                    if response.status_code == 200:
                        with open(os.path.join(folder_path, filename), 'wb') as file:
                            file.write(response.content)
                        success += 1
                    else:
                        fail += 1
                except Exception:
                    fail += 1
            
            # 2. Download Audio
            if download_audio and audio_files:
                audio_folder = os.path.join(folder_path, "REKAMAN")
                os.makedirs(audio_folder, exist_ok=True)
                record_base_url = "http://localhost/records"

                for filename in audio_files:
                    processed += 1
                    pct = (processed / total_items) * 100
                    self.root.after(0, update_ui, pct, f"Mengunduh Audio: {filename}")

                    try:
                        file_url = f"{record_base_url}/{filename}"
                        res = requests.get(file_url, stream=True)
                        if res.status_code == 200:
                            with open(os.path.join(audio_folder, filename), 'wb') as f:
                                for chunk in res.iter_content(chunk_size=8192):
                                    f.write(chunk)
                            success += 1
                        else:
                            fail += 1
                    except Exception:
                        fail += 1
            
            # Use a slightly delayed finish to show 100%
            self.root.after(0, update_ui, 100, "Selesai!")
            time.sleep(0.5)
            self.root.after(0, finish_ui, success, fail)

        threading.Thread(target=run_all_downloads, daemon=True).start()

    def finish_download(self, count):
        pass # Deprecated by internal logic in download

    def process_audio_download(self, start_date, end_date, save_folder):
        pass # Deprecated merged into download

    def backup(self):
        popup = tk.Toplevel(self.root)
        popup.title("Verifikasi")
        popup.geometry("350x200")
        self.center_window(popup)
        popup.configure(bg="#f8f9fa")

        # Container
        content_frame = tk.Frame(popup, bg="#f8f9fa", padx=20, pady=20)
        content_frame.pack(fill="both", expand=True)

        tk.Label(content_frame, text="Keamanan", font=("Arial", 12, "bold"), bg="#f8f9fa", fg="#333").pack(pady=(0, 10))
        tk.Label(content_frame, text="Masukkan password untuk melakukan Backup:", bg="#f8f9fa", font=("Arial", 10)).pack(pady=5)

        self.password_entry = tk.Entry(content_frame, show='*', font=("Arial", 10), width=25)
        self.password_entry.pack(pady=10)
        self.password_entry.focus()

        btn_frame = tk.Frame(content_frame, bg="#f8f9fa")
        btn_frame.pack(pady=10)

        # Confirm Button
        confirm_btn = tk.Button(btn_frame, text="Verifikasi", command=lambda: [self.verify_password(), popup.destroy() if self.password_verified else None],
                                bg="#3498db", fg="white", font=("Arial", 10), relief="flat", padx=15, pady=5)
        confirm_btn.pack(side="left", padx=5)

        # Cancel Button
        cancel_btn = tk.Button(btn_frame, text="Batal", command=popup.destroy,
                               bg="#e74c3c", fg="white", font=("Arial", 10), relief="flat", padx=15, pady=5)
        cancel_btn.pack(side="right", padx=5)

        # Helper state for popup logic
        self.password_verified = False

    def verify_password(self):
        entered_password = self.password_entry.get()
        correct_password = "12orangepi12"

        if entered_password == correct_password:
            self.password_verified = True
            try:
                # First ensure USB is there
                usb_drives = self.detect_usb()
                if not usb_drives:
                    messagebox.showerror("Error", "USB flashdisk tidak ditemukan!")
                    return

                # Get the process running the mysqldump
                # Ensure we handle the command correctly. It was split weirdly in previous code.
                command = ["mysqldump", "-u", "root", "ip-call"]
                # Note: Assuming password is empty or configured in .my.cnf, previous code had --password= empty.

                result = subprocess.run(command, capture_output=True, text=True) # removed check=True to handle error manually if needed
                
                if result.returncode != 0:
                     messagebox.showerror("Backup Error", f"MySQL Dump Failed:\n{result.stderr}")
                     return

                dump_output = result.stdout
                filename = f"backup_ip-call_{datetime.datetime.now().strftime('%Y-%m-%d_%H.%M.%S')}.sql"
                usb_drive_path = usb_drives[0]['mountpoint']
                folder_path = os.path.join(usb_drive_path, "BACKUP")
                os.makedirs(folder_path, exist_ok=True)
              
                with open(os.path.join(folder_path, filename), 'w') as file:
                    file.write(dump_output)
                messagebox.showinfo("Success", "Backup data berhasil disimpan di USB.")
            except Exception as e:
                messagebox.showerror("Error", f"Terjadi kesalahan saat backup: {str(e)}")

        else:
            self.password_verified = False
            messagebox.showerror("Akses Ditolak", "Password salah.")

    def check_usb(self):
        usb_drives = self.detect_usb()
        current_devices = {drive['device'] for drive in usb_drives}

        # Find new devices
        new_devices = current_devices - self.known_devices
        if new_devices:
            self.show_usb_window()

        # Find removed devices
        removed_devices = self.known_devices - current_devices
        if removed_devices:
            self.hide_usb_window()

        self.known_devices = current_devices
        self.root.after(self.check_interval * 1000, self.check_usb)

    def run(self):
        self.root = tk.Tk()
        self.root.withdraw()  # Hide root window
        
        # Removed theme use to stabilize X11
        # try:
        #     style = ttk.Style()
        #     style.theme_use('clam')
        # except Exception:
        #     pass


        # Exit mechanism (optional, for debugging or manual close)
        # In a headless/service environment, this might be handled differently, 
        # but since there is a UI, maybe we keep it hidden or minimized.
        
        signal.signal(signal.SIGINT, self.signal_handler)
        self.check_usb()
        self.root.mainloop()

    def signal_handler(self, signal, frame):
        self.root.quit()
        sys.exit(0)

    def center_window(self, window):
        window.update_idletasks()
        try:
            screen_width = window.winfo_screenwidth()
            screen_height = window.winfo_screenheight()
            
            # Allow geometry string parsing or fallback
            geom = window.geometry()
            try:
                # Format is usually WxH+X+Y
                wh, x, y = geom.split('+')
                w, h = wh.split('x')
                window_width = int(w)
                window_height = int(h)
            except:
                window_width = window.winfo_reqwidth()
                window_height = window.winfo_reqheight()

            # Fix for small defaults - handled by explicit geometry calls in caller
            # Only apply default if dimensions are suspiciously small (uninitialized)
            if window_width < 10: window_width = 800
            if window_height < 10: window_height = 480

            x = (screen_width // 2) - (window_width // 2)
            y = (screen_height // 2) - (window_height // 2)
            window.geometry(f'{window_width}x{window_height}+{x}+{y}')
        except Exception:
            pass

def parse_arguments():
    """Parse command line arguments."""
    parser = argparse.ArgumentParser(description='USB Monitor with Download/Backup')
    parser.add_argument(
        '--bg-file',
        type=str,
        default=DEFAULT_BG_FILE,
        help=f'Path to background image file (default: {DEFAULT_BG_FILE})'
    )
    return parser.parse_args()

if __name__ == "__main__":
    args = parse_arguments()
    print(f"💻 USB Monitor starting...")
    print(f"🖼️  Menggunakan background: {args.bg_file}")
    
    monitor = USBMonitor(bg_file=args.bg_file)
    monitor.run()
