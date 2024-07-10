import psutil
import tkinter as tk
from tkinter import messagebox
from tkcalendar import DateEntry
from PIL import Image, ImageTk
import signal
import sys
import datetime
import requests
import os
import logging

class USBMonitor:
    def __init__(self, check_interval=1):
        self.check_interval = check_interval
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
            # self.usb_window = tk.Toplevel(self.root, bg='white')
            # self.usb_window.title("USB Flashdisk Terdeteksi")
            # self.usb_window.geometry("300x150")  # Mengatur ukuran jendela
            # self.center_window(self.usb_window)  # Menempatkan jendela di tengah layar
            # self.usb_window.configure()  # Mengatur latar belakang jendela popup dengan warna biru

            # # Menggunakan frame untuk tata letak
            # frame = tk.Frame(self.usb_window, bg='white')  # Mengatur frame dengan warna biru
            # frame.pack(fill='both', expand=True, padx=20, pady=20)

            # today = datetime.date.today()


            # tk.Label(frame, bg='white', text="Start Date").grid(row=0, column=0, padx=5, pady=5)
            # self.start_date_entry = DateEntry(frame, maxdate=today, date_pattern='yyyy-mm-dd')
            # self.start_date_entry.grid(row=0, column=1, padx=5, pady=5)

            # tk.Label(frame, bg='white', text="End Date").grid(row=1, column=0, padx=5, pady=5)
            # self.end_date_entry = DateEntry(frame, maxdate=today, date_pattern='yyyy-mm-dd')
            # self.end_date_entry.grid(row=1, column=1, padx=5, pady=5)

            # # Download button
            # download_button = tk.Button(frame, text="Download", command=self.download, bg='white', fg='#3498db')
            # download_button.grid(row=2, column=0, columnspan=4, padx=10, pady=10)
        
            self.usb_window = tk.Toplevel(self.root, bg='white')
            self.usb_window.title("USB Flashdisk Terdeteksi")
            self.usb_window.geometry("400x250")
            self.usb_window.attributes('-alpha', 0.5)
            self.center_window(self.usb_window)

            # Load and place background image
            self.background_image = Image.open("/opt/lampp/htdocs/ip-call/assets/bg.JPEG")
            self.background_image = self.background_image.resize((400, 250), Image.ANTIALIAS)
            self.background_photo = ImageTk.PhotoImage(self.background_image)
            
            self.canvas = tk.Canvas(self.usb_window, width=400, height=250)
            self.canvas.pack(fill='both', expand=True)
            self.canvas.create_image(0, 0, image=self.background_photo, anchor='nw')

            # Create a frame for widgets
            frame = tk.Canvas(self.canvas, highlightthickness=0)
            frame.create_image(0, 0, image=self.background_photo, anchor='nw')
            frame.place(relx=0.5, rely=0.5, anchor='center')

            today = datetime.date.today()

            tk.Label(frame, bg='white', text="Start Date").grid(row=0, column=0, padx=5, pady=5)
            self.start_date_entry = DateEntry(frame, maxdate=today, date_pattern='yyyy-mm-dd')
            self.start_date_entry.grid(row=0, column=1, padx=5, pady=5)

            tk.Label(frame, bg='white', text="End Date").grid(row=1, column=0, padx=5, pady=5)
            self.end_date_entry = DateEntry(frame, maxdate=today, date_pattern='yyyy-mm-dd')
            self.end_date_entry.grid(row=1, column=1, padx=5, pady=5)

            download_button = tk.Button(frame, text="Download", command=self.download, bg='white', fg='#3498db')
            download_button.grid(row=2, column=0, columnspan=2, padx=10, pady=10)

    def hide_usb_window(self):
        if self.usb_window is not None:
            self.usb_window.destroy()
            self.usb_window = None

    def download(self):
        start_date = self.start_date_entry.get()
        end_date = self.end_date_entry.get()

        # Simulasi URL download dari internet (ganti dengan URL sesungguhnya)
        url_history_pdf = f"http://localhost/ip-call/server/history/pdf.php?start_date={start_date}&end_date={end_date}"
        url_history_excel = f"http://localhost/ip-call/server/history/excel.php?start_date={start_date}&end_date={end_date}"

        url_log_pdf = f"http://localhost/ip-call/server/log/pdf.php?start_date={start_date}&end_date={end_date}"
        url_log_excel = f"http://localhost/ip-call/server/log/excel.php?start_date={start_date}&end_date={end_date}"

        try:
            # Download file dari URL
            response_history_pdf = requests.get(url_history_pdf)
            response_history_excel = requests.get(url_history_excel)
            response_log_pdf = requests.get(url_log_pdf)
            response_log_excel = requests.get(url_log_excel)

            usb_drive_path = self.detect_usb()[0]['mountpoint']
            folder_name = f"DOWNLOAD_{datetime.datetime.now().strftime('%Y-%m-%d_%H.%M.%S')}"
            folder_path = os.path.join(usb_drive_path, folder_name)
            os.makedirs(folder_path, exist_ok=True)

            if response_history_pdf.status_code == 200:
                # Simpan file ke USB flashdisk
                filename = f"PDF_RIWAYAT_TELEPON_{start_date}~{end_date}.pdf"
                logging.info(f"DOWNLOAD {filename} SUCCESS")
                print(filename)
                with open(os.path.join(folder_path, filename), 'wb') as file:
                    file.write(response_history_pdf.content)
            else:
                messagebox.showerror("Error", f"{filename} Failed to download file.")
                logging.error(f"DOWNLOAD {filename} FAILED")
            
            if response_history_excel.status_code == 200:
                # Simpan file ke USB flashdisk
                filename = f"EXCEL_RIWAYAT_TELEPON_{start_date}~{end_date}.xlsx"
                logging.info(f"DOWNLOAD {filename} SUCCESS")
                print(filename)
                with open(os.path.join(folder_path, filename), 'wb') as file:
                    file.write(response_history_excel.content)
            else:
                messagebox.showerror("Error", f"{filename} Failed to download file.")
                logging.error(f"DOWNLOAD {filename} FAILED")
            
            
            if response_log_pdf.status_code == 200:
                # Simpan file ke USB flashdisk
                filename = f"PDF_LOG_{start_date}~{end_date}.pdf"
                logging.info(f"DOWNLOAD {filename} SUCCESS")
                print(filename)
                with open(os.path.join(folder_path, filename), 'wb') as file:
                    file.write(response_log_pdf.content)
            else:
                messagebox.showerror("Error", f"{filename} Failed to download file.")
                logging.error(f"DOWNLOAD {filename} FAILED")
            
            
            if response_log_excel.status_code == 200:
                # Simpan file ke USB flashdisk
                filename = f"EXCEL_LOG_{start_date}~{end_date}.xlsx"
                logging.info(f"DOWNLOAD {filename} SUCCESS")
                print(filename)
                with open(os.path.join(folder_path, filename), 'wb') as file:
                    file.write(response_log_excel.content)
            else:
                messagebox.showerror("Error", f"{filename} Failed to download file.")
                logging.error(f"DOWNLOAD {filename} FAILED")


            messagebox.showinfo("Success", "Download berhasil")

            self.hide_usb_window()
        except Exception as e:
            messagebox.showerror("Error", f"An error occurred: {str(e)}")
            logging.error(f"An error occurred: {str(e)}")


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
        self.root.withdraw()  # Menyembunyikan jendela root

        # Menambahkan exit button ke jendela utama
        exit_button = tk.Button(self.root, text="Exit", command=self.root.quit)
        exit_button.pack()

        # Handle signal
        signal.signal(signal.SIGINT, self.signal_handler)

        self.check_usb()
        self.root.mainloop()

    def signal_handler(self, signal, frame):
        logging.info("APLIKASI BERHENTI")
        self.root.quit()
        sys.exit(0)

    def center_window(self, window):
        window.update_idletasks()
        screen_width = window.winfo_screenwidth()
        screen_height = window.winfo_screenheight()
        window_width = window.winfo_reqwidth()
        window_height = window.winfo_reqheight()
        x = (screen_width // 2) - (window_width // 2)
        y = (screen_height // 2) - (window_height // 2)
        window.geometry(f'+{x}+{y}')  # Menyesuaikan dengan posisi tengah layar

if __name__ == "__main__":
    logging.basicConfig(filename='/home/nursecallserver/app.log', filemode='w', level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
    logging.info("Aplikasi berjalan")
    
    monitor = USBMonitor()
    monitor.run()
    logging.shutdown()
