Bootstrap Wrapper Plugin for DokuWiki
=====================================

Bootstrap Wrapper for DokuWiki

## Nested wrappers

Use lowercase `<wrapper>` for a block container. Wrappers can be nested:

```text
<wrapper class="text-center">
Outer content

<wrapper class="well">
Inner content
</wrapper>

More outer content
</wrapper>
```

By default a wrapper produces a `<div>`. Use `inline="true"` to style text inside
a sentence without breaking the paragraph:

```text
Status: <wrapper inline="true" class="text-danger">Out of service</wrapper> today.
```

Inline wrappers produce `<span>` elements and can contain other inline wrappers.
Block wrappers can contain both block and inline wrappers. Keep block content
(paragraphs, headings, lists and tables) outside inline wrappers.

Both forms accept the usual `class`, `id` and `style` attributes. Uppercase
`<WRAPPER>` is no longer supported; change existing tags to lowercase. For older
lowercase wrappers used within sentences, add `inline="true"` to retain inline
formatting.

All documentation for this plugin can be found at
http://www.dokuwiki.org/plugin:bootswrapper

If you install this plugin manually, make sure it is installed in
lib/plugins/icons/ - if the folder is called different it
will not work!

Please refer to http://www.dokuwiki.org/plugins for additional info
on how to install plugins in DokuWiki.

----
Copyright (C) Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the COPYING file in your DokuWiki folder for details

