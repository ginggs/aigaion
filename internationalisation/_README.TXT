This directory contains the files needed for translation of Aigaion into new languages. 

Translations are prepared in this directory; when a translation is complete, it can be added to the aigaionengine repository, or released as a language pack on SourceForge. Of course, this should only happen after the new translations have been thoroughly tested.

=== Contents of this directory ===
- messages.pot:
      The master template with all Aigaion messages of the most recent (SVN) version of Aigaion
- getmessages.bat
  update_pot
      Two scripts to update messages.pot with new messages from the Aigaion source code
- /<lang>/
      The directory containing the translation files of a language for which a translation 
      of the current version of Aigaion is finished or being made.
- /<lang>/LC_MESSAGES/messages.po
      Translation for a specific language
- /<lang>/LC_MESSAGES/messages.mo
      Compiled version of a translation, which can be used in Aigaion
- /Aigaion<version>/locale/
      The directory in which translations of older versions of Aigaion are being maintained.
      We will not maintain translations of every language for every version of Aigaion. Instead, 
      translations of older versions will only be maintained when people explicitly need/want this.
