# Pancake

A tasty flat-file CMS backend

## What you can do with it

1. Create a folder called `content` in your app root.
2. Place the `api` folder in your app root.
3. Make some Markdowns.
4. Give your Markdowns some YAML front matter.
5. Save your Markdowns in the content folder. Use whatever directory structure you want, like `/content/posts/my-favorite-things/cats.md`.
6. Get some JSON from `/api/posts/my-favorite-things/cats/`
7. Make a frontend, you lazy-ass.

### JSON key-value pairs

`meta`: A JSON object containing the YAML front matter key-value pairs

`content`: A string of the Markdown content of your file, converted to HTML

`items`: If the request is to a directory, Pancake loads the index.md into `meta` and `content` and the directory contents into this array of items. Each item is a JSON object with "meta" and "content" keys.

### Optional parameters

`content=(true|false)`
`items=(true|false)`
`with=(all|comma separated list of metadata fields)`
`without=(none|comma separated list of metadata fields)`
