# Todos CMSOJ 

## lightBlog

### Schema

blog_topics table
- id
- title
- last_post

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
- updated_at

