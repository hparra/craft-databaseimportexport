# Database Import/Export

## About

Database allows you to import and export select Craft schema and data as JSON. The additional processing of these JSON files allows for extensive reuse across different Craft installations. Code your complex Field once and use it again in your other projects!

Currently Database can only be used via the command-line interface of the installed server. Future releases will include an elaborate user interface, but the command-line interface will always remain for console users and scripts, such as cron jobs and server provisioners like Puppet and Chef.

# Usage

Exporting will create the following directories in the specified path:

* Schema
* FieldGroups
* Fields
* Data
* Entries

Import routine will expect JSON schema to be in these same directories, however, in the case of Fields, subdirectories created by exporting are not necessary. Export does this as a convenience for developers.

It is recommended you store imported/exported data in a folder, e.g. "db" outside your craft directory. and use identifiable directory names, e.g. "FieldGroups".

## Fields

The `fields` command handles the import/export of Fields and FieldGroups

To see options with fields command:

`./craft/plugins/database/yiic fields`

### Exporting

* exportAllGroups
* exportAllFields
* exportFieldByHandle
* exportFieldByName

Example: To export all field groups to a directory 'db/schema/FieldGroups/"

`./craft/plugins/database/yiic fields exportAllGroups --path=db/schema/FieldGroups/`

### Importing

* importField
