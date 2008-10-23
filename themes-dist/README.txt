------------------------
About Themes
------------------------

Segue themes may be stored in the file-system (base themes) as well as in the 
database (user themes). Base themes are read-only in Segue and can serve as a 
starting point for user-created themes. Base themes can be modified only by the
Segue system administrator, not through the Segue user-interface. User-created 
themes are stored wholly in the database.

------------------------
Base Themes
------------------------
Segue has two base-theme directories:
	segue/themes-dist/
	segue/themes-local/

Themes that ship with Segue live in themes-dist/ and themes created by you should
live in the themes-local/ directory. If you create a theme in the themes-local/ directory
of the same name as one in the themes-dist/ directory, the themes-local/ version
will be used instead.
