# local_th_slideshow

A Moodle local plugin that provides a configurable frontpage slideshow (matching the slider in **theme_lambda**) **and a Mobile Web Service API** so native apps can fetch slide data directly.

## Features

- **5 independent slides**, each with:
  - Upload image (stored file)
  - Title text
  - Caption / sub-text (textarea)
  - Optional click-through URL
- **Options page**:
  - Height: 375 px → 575 px, or Responsive
  - Hide captions on mobile
  - Overlay pattern (none / pattern1–4)
  - Auto-advance toggle
  - Navigation arrows / dot toggle
  - Loader style: bar / pie / none
  - Image transition effect (e.g. `random`, `fade`, `slideLeft`)
  - Text transition effect (e.g. `moveFromLeft`, `fadeIn`)

---

## Features

- **5 independent slides**, each with:
  - Upload image (stored file, served via `pluginfile.php`)
  - Title text
  - Caption / sub-text (HTML textarea)
  - Optional click-through URL
- **Options page**: height, hide-captions-on-mobile, overlay pattern, auto-advance, navigation, loader style, image FX, text FX
- **Web Service API**: `local_th_slideshow_get_slides` — returns all slide data + options as JSON (no login required, AJAX-enabled)

---

## Installation

1. Copy the `local_th_slideshow` folder into `<moodle_root>/local/`.
2. Visit **Site administration → Notifications** and complete the upgrade.
3. Go to **Site administration → Plugins → Local plugins → TH Slideshow** to configure.

### Enabling Web Services (first time only)

1. **Site administration → Advanced features** → tick **Enable web services** → Save.
2. **Site administration → Server → Web services → Overview** → enable the **REST** protocol.
3. The service **"TH Slideshow Mobile API"** (`local_th_slideshow_mobile`) is created automatically. Verify it is enabled at **Server → Web services → External services**.
4. To use token authentication, create a token at **Server → Web services → Manage tokens**.

---

## Web Service API

### Endpoint

```
GET/POST  https://<your-moodle>/webservice/rest/server.php
```

### Parameters

| Parameter | Value |
|-----------|-------|
| `wsfunction` | `local_th_slideshow_get_slides` |
| `moodlewsrestformat` | `json` |
| `wstoken` | *(token — omit if `loginrequired` is false and guest access is OK)* |

### Example request (curl)

```bash
curl "https://moodle.example.com/webservice/rest/server.php" \
  --data "wsfunction=local_th_slideshow_get_slides&moodlewsrestformat=json&wstoken=YOUR_TOKEN"
```

### Example response

```json
{
  "slides": [
    {
      "index": 1,
      "image": "https://moodle.example.com/pluginfile.php/1/local_th_slideshow/slide1image/0/banner.jpg",
      "title": "Welcome to our LMS",
      "caption": "<p>Start learning today!</p>",
      "url": "https://moodle.example.com/course/index.php"
    },
    {
      "index": 2,
      "image": "https://moodle.example.com/pluginfile.php/1/local_th_slideshow/slide2image/0/courses.jpg",
      "title": "Browse Courses",
      "caption": "",
      "url": ""
    }
  ],
  "options": {
    "height": "475px",
    "hide_captions": false,
    "pattern": "0",
    "auto_advance": true,
    "show_nav": true,
    "loader": "bar",
    "imgfx": "random",
    "txtfx": "moveFromLeft"
  }
}
```

> **Note**: Only slides that have an uploaded image are included in the response.

---

## Usage in a theme / block (server-side)

```php
// Via renderer:
$renderer = $PAGE->get_renderer('local_th_slideshow');
echo $renderer->render_slideshow();

// Via template directly:
require_once($CFG->dirroot . '/local/th_slideshow/lib.php');
$data = local_th_slideshow_get_template_data();
echo $OUTPUT->render_from_template('local_th_slideshow/slideshow', $data);
```

---

## File structure

```
local_th_slideshow/
├── classes/
│   ├── external/
│   │   └── get_slides.php          ← Web service external function
│   ├── output/
│   │   └── renderer.php            ← Theme renderer
│   └── privacy/
│       └── provider.php            ← GDPR null provider
├── db/
│   ├── access.php                  ← Capabilities
│   └── services.php                ← WS function + service registration
├── lang/
│   └── en/
│       └── local_th_slideshow.php  ← Language strings
├── templates/
│   └── slideshow.mustache          ← Mustache template
├── lib.php                         ← Helper functions + pluginfile handler
├── settings.php                    ← Admin settings (2 pages)
├── version.php
└── README.md
```

---

## License

GNU GPL v3 or later — https://www.gnu.org/licenses/gpl-3.0.html
