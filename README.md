# BB_TinyFileManager Module

## Overview
This module replaces the default browse and upload section in OpenMage/Magento dataflow import profiles with [TinyFileManager](https://github.com/prasathmani/tinyfilemanager), providing a powerful web-based file management interface.

## Features
- **Full-featured file manager** - Upload, download, rename, delete, edit files
- **Directory management** - Create and organize directories
- **Multiple file uploads** - Upload multiple files at once
- **File preview** - View files directly in the browser
- **Text file editing** - Edit CSV and text files directly
- **Search functionality** - Find files quickly
- **Modern interface** - Clean, responsive design

## Usage

1. Navigate to: **System > Import/Export > Dataflow - Profiles**
2. Select or create an import profile
3. Click on the **Upload File** tab
4. Use the file manager interface to:
   - Upload files by clicking the upload button
   - Create folders using the new folder button
   - Manage existing files (rename, delete, download, edit)
   - Navigate through directories

## File Manager Features

### Upload Files
- Click the "Upload" button in the toolbar
- Select one or multiple files
- Files will be uploaded to the current directory

### Create Directories
- Click the "New Folder" button
- Enter a folder name
- Organize your import files by product type, date, etc.

### File Operations
- **View**: Click on a file name to preview
- **Edit**: Click the edit icon for text/CSV files
- **Download**: Click the download icon
- **Rename**: Click the rename icon
- **Delete**: Click the delete icon (with confirmation)

### Search
- Use the search box to find files quickly
- Searches in current directory and subdirectories

## License

This module is provided as-is for integration purposes. TinyFileManager is licensed under GNU GPL v3.

## Version

- Module Version: 1.0.0
- Compatible with: OpenMage 19.x, 20.x
- TinyFileManager: Latest from GitHub

## Changelog

### 1.0.0 (2026-03-18)
- Initial release
- Basic TinyFileManager integration
- Replace default upload interface
- Support for all TinyFileManager features
