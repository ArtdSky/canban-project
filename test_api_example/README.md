# Тестирование API

Эта директория содержит HTTP файлы для тестирования API endpoints.

## Использование

1. Используйте расширение для VS Code: **REST Client** (humao.rest-client)
2. Или любой другой инструмент, поддерживающий `.http` файлы

## Структура директории

- `login_and_reg/` - файлы для тестирования авторизации и регистрации
  - `register.http` - регистрация пользователей
  - `login.http` - авторизация пользователей
  - `logout.http` - выход из системы
- `user.http` - получение данных текущего пользователя

## Порядок тестирования

1. **login_and_reg/register.http** - Сначала зарегистрируйте пользователя
2. **login_and_reg/login.http** - Авторизуйтесь и скопируйте полученный токен
3. Установите токен в переменную `{{token}}` в файлах `user.http` и `login_and_reg/logout.http`
4. **user.http** - Проверьте получение данных пользователя
5. **login_and_reg/logout.http** - Проверьте выход из системы

## Переменные

Переменные объявлены в начале каждого `.http` файла:

```http
@baseUrl = http://localhost:8080
@token = your-token-here
```

### Настройка токена:

1. Выполните запрос на логин (`login.http`)
2. Скопируйте `token` из ответа
3. В файлах `login.http`, `user.http` и `logout.http` замените `your-token-here` на полученный токен:

```http
@token = 1|RLIME8BtnCQhrUJzZLzpSzZx6Cjxm0NmgnMCCyK0f5ef77b3
```

## Пример ответа при успешной регистрации:

```json
{
  "message": "Пользователь успешно зарегистрирован. Для авторизации используйте /api/login",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  }
}
```

## Пример ответа при успешной авторизации:

```json
{
  "message": "Успешная авторизация.",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

## Использование в VS Code

1. Установите расширение **REST Client** (humao.rest-client)
2. Откройте любой `.http` файл
3. Нажмите кнопку **Send Request** над запросом или используйте `Ctrl+Alt+R` (или `Cmd+Alt+R` на Mac)
4. Результат отобразится в новой вкладке

