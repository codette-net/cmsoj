# Todos CMSOJ 

## lightBlog

### Schema

blog_topics table
- id
- title
- title_slug
- last_post
..?

posts table
- id
- page_id (0 = default , main)
- title (11 words 65 characters)
- slug
- keywords
- introduction (1 - 3 paragraphs)
- Description
- cover img (default = logo.png)
- author_id (user_id, default = 1)
- status (published, draft, archived)
- created_at
- published_at
- updated_at
..?

media table (usable for blog and others)
- id
- title
- type
- approved (default true)
- file_url
- thumbnail_url
- created_at
- updated_at
..?


### todos blog
make dummy posts
routes web & admin
controller
model
public views , (/blog, /blog/post/{id}, /blog/topic/{title}, etc)
admin dash views

tinyMCE integration
media handling 
