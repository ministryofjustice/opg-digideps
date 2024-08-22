### Audit packages

The audit packages script takes as input the `yml` files for each required scan.

You can modify parameters like files you want to search in a more lax way on and manually checked
exceptions. For example some packages are in the .yml files but they don't include the provider
part of the name but they do include the other parts so the lax search doesn't require 'use' or
the provider to be in the line to find a match but only for file types you provide in
the lax-search-extensions.

At this moment this does an audit of composer packages. Whilst we can find the true path using
autoload file in vendor folders, we wanted to be able to check to the best of our ability without
installing all the packages just based on the composer.json. As such there may be improvements in
the future to make this even more accurate. It's pretty accurate as it is as the names are almost always
in the format of provider / words from the package name.
