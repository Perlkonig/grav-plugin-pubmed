# Pubmed Plugin

The **Pubmed** Plugin is for [Grav CMS](http://github.com/getgrav/grav). It uses the [National Center for Biotechnology Information's](https://www.ncbi.nlm.nih.gov/) [E-utils API](https://www.ncbi.nlm.nih.gov/books/NBK25500/) to access pubmed information about publications. This is accomplished by passing UIDs via a special shortcode.

For a demo, [visit my blog](https://perlkonig.com/demos/pubmed).

## Installation

Installing the Pubmed plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install pubmed

This will install the Pubmed plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/pubmed`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `pubmed`. You can find these files either on [GitHub](https://github.com/Perlkonig/grav-plugin-pubmed) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/pubmed
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav), the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) plugins, and a theme to be installed in order to operate.

## Usage

The shortcode contains the following parts:

  - The string `[pubmed>`
  - Followed by one of the three commands:
    - `short`
    - `long`
    - `summary`
  - Followed by a colon (`:`)
  - Followed by a list of UIDs separated by commas (no spaces!)
  - And finally a closing bracket (`]`)

The following are valid shortcodes:

  - `[pubmed>short:11111]`
  - `[pubmed>long:11111,22222]`
  - `[pubmed>summary:11111,22222,33333]`

These are block elements. Each shortcode is replaced by a `<div>` containing the data. 

The expanded shortcode *is* cached. The UID string and results are also cached so that if you have multiple pages citing the same UID string, only one external API call will be made.

You can customize the exact contents of the `short` and `long` citation as described in the **Configuration** section. The `summary` report is a raw dump of the returned JSON data from the E-utils service.

If an error occurs, the `short` or `long` output will be replaced by a message stating there was a problem fetching that UID.

## Configuration

The default configuration is as follows:

```
enabled: true
active: false

# Formats are a string that only contain 
#   - valid field names enclosed in square brackets
#   - line breaks encoded as the string "\n"
#   - other literals (printed as is)
#
# Valid field names are as follows (case sensitive!)
#   - uid
#   - title
#   - authors_long
#   - authors_short
#   - journal
#   - volume
#   - pages
#   - date
#
# Raw fields are fields not embedded into <span></span> tags.
# They are suitable to build links, for instance.

formats:
  short: "[authors_short] [journal] [volume] [pages] [date]"
  long: "<a href='https://www.ncbi.nlm.nih.gov/pubmed/[uid raw]'>[title]</a>\n[authors_long] [journal] [volume] [pages] [date]"
  author_sep: ", "  # string that is inserted between list of authors when using [authors_long]
```

To change the configuration, copy `pubmed.yaml` from the `plugins/pubmed` folder into your `config/plugins` folder. That way, if the plugin gets updated, your custom configuration is not lost.

- `enabled` tells Grav to run the plugin. If you set this to `false`, the plugin will be fully disabled and the shortcodes will print as is.

- `active` makes it possible to only run the plugin on specific pages. In the header of the page that includes the shortcode, put the following in the header:

  ```
  pubmed:
    active: true
  ```

  Alternatively, you can set `active` to `true` in the base config and the plugin will search all pages for the shortcode.

- `formats` gives you flexibility over how the `short` and `long` versions are formatted. The embedded instructions are hopefully self-explanatory.

## Customization

Each element of the citation has a class assigned to it for easy styling. Copy the `pubmed.css` file from the `plugins/pubmed/assets` folder and add it to your theme's ``assets`` folder. Edit as you see fit.

