# php-markdown-preprocessor


**DRAFT**!!!
This gizmore RFC draft is in development phase.

---

Convert and edit markdown-like files;
Extend them with Smileys.
Add custom patterns like `%aa%username_here%`.
Transpile to ASCIICode, youTube description and Markdown.

Used in my *CI*. :)

(TODO) Apply a permission system for wc somehow.



## Own Additions

A) I am tired of typing the same URLs all over again:

 - `%yt%@channel%`: Link to a YouTube channel.
 - `%w%searchterm%`: Link to a Wikipedia page.
 - `%tw%@channel%`: Link to a Twitter account.
 - `%tw%#tweet%`: Link to a tweet.
 - `%wc%username%`: Link to a [WeChall](https://www.wechall.net) username.
 - @**TODO**: Allow custom url patterns... (Or lets make an official list ^-^)
 

B) Better readable source:
 
 - `%%`: Converts into `"<br/>\n"`.
 
 
C) Smileys like `:)` can be ignored or become:
github smileys, markdown images, utf8smileys.
 
 
D) Ideas?
 
 - `%dd%gizmore.org%`: make a link to `//gizmore.org`. Put a `![text](url)` markdown image preview beside the link.
 
 
---

## Usage




#### License MIT
##### (c)2023 gizmore@wechall.net
