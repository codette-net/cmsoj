# Schema (tight + practical)
blog_topics

id INT PK AI

title VARCHAR(120) NOT NULL

title_slug VARCHAR(140) NOT NULL UNIQUE

description VARCHAR(255) NULL

last_post_at DATETIME NULL

created_at DATETIME NOT NULL

updated_at DATETIME NULL

posts

id INT PK AI

page_id INT NOT NULL DEFAULT 0

topic_id INT NULL (FK blog_topics.id)

title VARCHAR(80) NOT NULL (your “~65 chars” goal fits)

slug VARCHAR(140) NOT NULL UNIQUE

keywords VARCHAR(255) NULL

introduction TEXT NULL

description VARCHAR(255) NULL

content MEDIUMTEXT NOT NULL (TinyMCE content)

cover_media_id INT NULL (FK media.id) (better than raw string url)

author_id INT NOT NULL DEFAULT 1

status ENUM('published','draft','archived') NOT NULL DEFAULT 'draft'

created_at DATETIME NOT NULL

published_at DATETIME NULL

updated_at DATETIME NULL

Indexes:

posts(status, published_at)

posts(topic_id)

posts(slug) unique

media (shared)

id INT PK AI

title VARCHAR(160) NULL

type VARCHAR(50) NOT NULL (image/jpeg, image/png, etc)

approved TINYINT(1) NOT NULL DEFAULT 1

file_url VARCHAR(255) NOT NULL

thumbnail_url VARCHAR(255) NULL

created_at DATETIME NOT NULL

updated_at DATETIME NULL

# File/Folder placement (matches CMSOJ conventions)


CMSOJ/Models/Post.php

CMSOJ/Models/BlogTopic.php

CMSOJ/Models/Media.php

CMSOJ/Controllers/BlogController.php

CMSOJ/Controllers/Admin/BlogController.php

CMSOJ/Controllers/Admin/MediaController.php

CMSOJ/Routes/web.php add blog routes

CMSOJ/Routes/admin.php add admin blog routes (protected by AdminAuth)

Views:

CMSOJ/Views/blog/index.html

CMSOJ/Views/blog/post.html

CMSOJ/Views/blog/topic.html

CMSOJ/Views/admin/blog/index.html

CMSOJ/Views/admin/blog/edit.html

CMSOJ/Views/admin/media/index.html (picker / upload)