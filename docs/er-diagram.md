# BookShelf ER図

```mermaid
erDiagram

    USERS {
        bigint id PK
        varchar name
        varchar email
        timestamp email_verified_at
        varchar password
        varchar remember_token
        timestamp created_at
        timestamp updated_at
    }

    BOOKS {
        bigint id PK
        bigint user_id FK
        varchar title
        varchar author
        varchar isbn
        date published_date
        text description
        varchar image_url
        timestamp created_at
        timestamp updated_at
    }

    REVIEWS {
        bigint id PK
        bigint user_id FK
        bigint book_id FK
        tinyint rating
        text comment
        timestamp created_at
        timestamp updated_at
    }

    GENRES {
        bigint id PK
        varchar name
        timestamp created_at
        timestamp updated_at
    }

    FAVORITES {
        bigint id PK
        bigint user_id FK
        bigint book_id FK
        timestamp created_at
        timestamp updated_at
    }

    REVIEW_LIKES {
        bigint id PK
        bigint user_id FK
        bigint review_id FK
        timestamp created_at
        timestamp updated_at
    }
    
    BOOK_GENRE {
        bigint id PK
        bigint book_id FK
        bigint genre_id FK
        timestamp created_at
        timestamp updated_at
    }

    USERS ||--o{ BOOKS
    USERS ||--o{ REVIEWS

    BOOKS ||--o{ REVIEWS

    BOOKS ||--|{ BOOK_GENRE
    GENRES ||--o{ BOOK_GENRE

    USERS ||--o{ FAVORITES
    BOOKS ||--o{ FAVORITES

    USERS ||--o{ REVIEW_LIKES
    REVIEWS ||--o{ REVIEW_LIKES
```