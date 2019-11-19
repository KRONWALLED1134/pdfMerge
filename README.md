# PdfMerge

This plugin is intended to provide the possibility to merge manuscript submission files into one PDF file.
For this a button is added to the submission / review workflow tabs when viewing a submission.

### Prerequisites

- OJS 3.0.x
- Libreoffice
- Docker

### Installing

Move the pdfMerge folder to your plugins/generic directory

```
mv plugins/generic/pdfMerge /path/to/your/ojs/installation/plugins/generic
```

Move the pdfMerge folder to your api/v1 directory

```
mv api/v1/pdfMerge /path/to/your/ojs/installation/api/v1
```

Register the plugin

```
php /path/to/your/ojs/installation/lib/pkp/tools/installPluginVersion.php plugins/generic/pdfMerge/version.xml
```

Add hook calls to the pages you want to show the plugin on. Please note that on every page you want to show this a submission and a stage id should be available.

```
{call_hook name="PdfMerge::Show" submissionId=$submission->getId() stageId=$stageId}
```

To display on the workflow submission / review pages you would include it in the following files

```
lib/pkp/templates/controllers/tab/workflow/submission.tpl
lib/pkp/templates/workflow/reviewRound.tpl
```

inside the ```pkp_context_sidebar``` div.

Edit docker-compose.yml

```
...
ports:
  - '5000:5000'
...
  - <Your OJS private file directory>:/var/www/files
...
```
Start the converter service 

```
cd converter && docker-compose up -d
```

Enable the plugin inside your OJS by going to

```
Management > Website Settings > Plugins > Generic Plugins and enable the Checkbox beside "PdfMerge"
```

## Contributing

If you want to contribute to this plugin please open a pull request.

## Authors

* **Torben Richter** - *Initial work*

See also the list of [contributors](https://github.com/KRONWALLED1134/pdfMerge/contributors) who participated in this project.

## License
This project is licensed under the GNU General Public License v2 - see the [LICENSE](LICENSE) file for details

## Acknowledgments

* All of the guys working on OJS!
