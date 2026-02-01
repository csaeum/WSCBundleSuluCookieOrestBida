# Orest Bida Cookie Consent Assets

This bundle requires the Orest Bida Cookie Consent library.

## Installation

Download the files from https://github.com/orestbida/cookieconsent and place them here:

- `css/cookieconsent.css` - The CSS file
- `js/cookieconsent.umd.js` - The UMD JavaScript bundle

Or install via npm and copy the files:

```bash
npm install vanilla-cookieconsent
cp node_modules/vanilla-cookieconsent/dist/cookieconsent.css src/Resources/public/css/
cp node_modules/vanilla-cookieconsent/dist/cookieconsent.umd.js src/Resources/public/js/
```

After adding the files, run:

```bash
php bin/console assets:install --symlink
```
