# PHPTombo - a Tombo implementation in PHP

## What?
For my history with Tombo, see https://github.com/peturdainn/QTombo

At some point I wanted to have access to my files on the go. First idea was a mobile client and sync the tree. But then I created this PHP version and put it on my server, that works as well :)

## Getting started
Dump the thing in a folder on your webserver and create some authentication for it, because PHPTombo doesn't care about that!

Then, find /config/tomboconfig en put the path to your tombo root in there. Yeah, that means your tree should be on the same server AND the webserver needs access to it.

That's it really. For encrypted notes it will put the focus on the key input field. Press save or CTRL-S to save the note. Change the encryption key or leave it to save with the same one.
