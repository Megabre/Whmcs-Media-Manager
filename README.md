# Whmcs-Media-Manager
# WHMCS Media Manager

A lightweight, secure, and practical file management tool specifically designed for WHMCS admin panels. This tool allows you to manage images and documents directly from your admin directory without needing FTP access.

## Features

* **Direct Integration:** Works as a single PHP file. No module installation or complex configuration required.
* **Native Security:** Uses WHMCS's internal admin authentication system. Only logged-in administrators can access the manager.
* **Folder Management:** Create and delete folders to keep your media organized.
* **File Operations:** Upload, rename, and delete files with ease.
* **Quick URL Copy:** Get the direct URL of any uploaded file with a single click for use in knowledgebase articles or product descriptions.

## Installation

1. Download the `media-manager.php` file.
2. Open the file with a text editor and replace the placeholder `websiteniz.com` with your own domain name.
3. Upload the `media-manager.php` file into your WHMCS **admin** directory.
4. Access the tool via `yourdomain.com/admin/media-manager.php`.

## Usage

This tool is designed to eliminate the need for FTP when:
* Adding images to Knowledgebase articles.
* Uploading downloadable files for products.
* Managing announcement banners.
* Handling any media assets within the WHMCS ecosystem.

## Security Note

Since the script includes `admin/functions.php`, it inherits the session validation of your WHMCS installation. If you are not logged in as an administrator, the script will not execute, ensuring your files remain private.

---
Developed by Megabre
