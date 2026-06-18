# TH upload user validate email

Validate email when uploading email with csv file

## 🔒 Features

- Do not download users with wrong email format and download users with wrong email format.

## 🛠 Installation

1. Copy this plugin folder to `moodle/local/th_uploaduser_validate_email`
2. Go to `Site administration > Notifications` to complete installation
3. Navigate to `Site administration > Plugins > Local plugins > TH upload user validate email` to configure

## ⚙ Configuration

- **Enable**: Turn the plugin on or off

## ✅ Compatibility

Tested on Moodle:
- 3.10
- 4.1 (LTS)

## 📂 File structure

local/th_uploaduser_validate_email/
├── classes/
│   └── preview.php
│   └── user_form.php
├── lang/
│   └── en/
│       └── local_th_uploaduser_validate_email.php
├── lib.php
├── settings.php
└── version.php

## 📌 Notes

- The plugin does not change any core file
- It uses output buffering to strip the "From2" upload user with error validate email
- If the plugin is disabled upload user validate email

## 🧑‍💻 Author

TH Vietnam Solution and Technology Limited