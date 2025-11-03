# Руководство по запуску проекта

## Предварительные требования

- Docker и Docker Compose установлены
- Порт 8080 свободен (для Nginx)
- Порт 5432 свободен (для PostgreSQL)
- Порт 5173 свободен (для Vue dev server)

## Шаги запуска

### 1. Сборка контейнеров (Build)

Соберите Docker образы для всех сервисов:

```bash
docker compose build
```

Или для конкретного сервиса:

```bash
docker compose build app
docker compose build php
```

**Примечание:** При первом запуске сборка может занять некоторое время, так как загружаются базовые образы и устанавливаются зависимости.

### 2. Запуск контейнеров

Запустите все контейнеры в фоновом режиме:

```bash
docker compose up -d
```

Или для запуска конкретных сервисов:

```bash
docker compose up -d pg redis php app
```

Проверьте статус контейнеров:

```bash
docker compose ps
```

### 3. Установка зависимостей Composer

Установите PHP зависимости для Laravel:

```bash
docker compose run --rm composer install
```

**Примечание:** Флаг `--rm` автоматически удалит контейнер после выполнения команды.

### 4. Настройка окружения

Скопируйте файл окружения (если еще не создан):

```bash
cp .env.example .env
```

Сгенерируйте ключ приложения:

```bash
docker compose run --rm artisan key:generate
```

### 5. Выполнение миграций

Примените миграции базы данных:

```bash
docker compose run --rm artisan migrate
```

Или с подтверждением (если нужно):

```bash
docker compose run --rm artisan migrate --force
```

**Примечание:** Если нужно очистить базу и выполнить миграции заново:

```bash
docker compose run --rm artisan migrate:fresh
```

### 6. Установка зависимостей npm

Установите зависимости для Vue приложения:

```bash
docker compose run --rm npm install --prefix vue-app
```

Или через сервис npm:

```bash
docker compose run --rm npm --prefix vue-app install
```

### 7. Запуск сидеров

**Рекомендуемый способ (одна команда для всех сидеров):**

```bash
docker compose run --rm artisan seed:all
```

Эта команда запустит все сидеры по очереди:
- UserSeeder (5 пользователей)
- TaskSeeder (1 задача с участниками)
- CommentSeeder (1 комментарий)

И выведет подробную информацию о выполнении каждого шага.

**Альтернативные способы:**

Заполните базу данных через стандартный DatabaseSeeder:

```bash
docker compose run --rm artisan db:seed
```

Или запустите конкретный сидер:

```bash
# Только пользователи
docker compose run --rm artisan db:seed --class=UserSeeder

# Только задачи
docker compose run --rm artisan db:seed --class=TaskSeeder

# Только комментарии
docker compose run --rm artisan db:seed --class=CommentSeeder
```

### 8. Запуск Vue dev server

Vue dev server должен автоматически запуститься при выполнении `docker compose up -d vue`.

Если нужно запустить вручную:

```bash
docker compose up vue
```

## Проверка работоспособности

### API

API доступен по адресу: `http://localhost:8080/api`

Проверьте доступность:

```bash
curl http://localhost:8080/api/up
```

### Vue приложение

Vue dev server доступен по адресу: `http://localhost:5173`

### База данных

PostgreSQL доступна на:
- **Host:** `localhost`
- **Port:** `5432`
- **Database:** `canban`
- **User:** `root`
- **Password:** `root`

## Полезные команды

### Просмотр логов

```bash
# Все сервисы
docker compose logs -f

# Конкретный сервис
docker compose logs -f php
docker compose logs -f app
docker compose logs -f vue
```

### Остановка контейнеров

```bash
docker compose stop
```

### Остановка и удаление контейнеров

```bash
docker compose down
```

### Остановка с удалением volumes (ОСТОРОЖНО: удалит данные БД!)

```bash
docker compose down -v
```

### Выполнение Artisan команд

```bash
# Любая команда Artisan
docker compose run --rm artisan <команда>

# Примеры:
docker compose run --rm artisan cache:clear
docker compose run --rm artisan config:clear
docker compose run --rm artisan route:list
```

### Выполнение команд внутри контейнера

```bash
# PHP контейнер
docker compose exec php bash

# Nginx контейнер
docker compose exec app sh
```

## Порядок полного запуска проекта (краткая версия)

```bash
# 1. Сборка
docker compose build

# 2. Запуск
docker compose up -d

# 3. Установка Composer зависимостей
docker compose run --rm composer install

# 4. Генерация ключа (если нужно)
docker compose run --rm artisan key:generate

# 5. Миграции
docker compose run --rm artisan migrate

# 6. Установка npm зависимостей
docker compose run --rm npm install --prefix vue-app

# 7. Сидеры (рекомендуется использовать команду seed:all)
docker compose run --rm artisan seed:all
```

## Структура создаваемых данных (сидеры)

После выполнения сидеров в базе будут:

- **5 пользователей:**
  - Иван Иванов (`ivan@example.com`)
  - Мария Петрова (`maria@example.com`)
  - Петр Сидоров (`petr@example.com`)
  - Анна Козлова (`anna@example.com`)
  - Сергей Волков (`sergey@example.com`)
  - Пароль для всех: `password`

- **1 задача:**
  - Название: "Разработать систему управления задачами"
  - Создатель: Иван Иванов
  - Исполнитель: Анна Козлова
  - Наблюдатели: Мария Петрова, Петр Сидоров

- **1 комментарий:**
  - Автор: Мария Петрова
  - Текст: "сделайте предварительную оценку"

## Решение проблем

### Порт занят

Если порт занят, измените порты в `docker-compose.yml`:

```yaml
ports:
  - "8081:80"  # Вместо 8080:80
```

### Проблемы с правами доступа

Убедитесь, что UID и GID в `.env` соответствуют вашему пользователю:

```bash
echo "UID=$(id -u)" >> .env
echo "GID=$(id -g)" >> .env
```

Затем пересоберите:

```bash
docker compose build --no-cache
```

### База данных не доступна

Проверьте, что контейнер PostgreSQL запущен:

```bash
docker compose ps pg
docker compose logs pg
```

### Vue dev server не запускается

Проверьте логи:

```bash
docker compose logs vue
```

Убедитесь, что зависимости установлены:

```bash
docker compose run --rm npm install --prefix vue-app
```

