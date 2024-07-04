# Добро пожаловать в тестовый Symfony проект!

## Что вам нужно для начала работы?

Для запуска этого проекта вам понадобится Docker Compose и пакеты Composer.

Как установить Docker Compose?

	1.	Перейдите на официальный сайт Docker.
	2.	Скачайте и установите Docker Desktop для вашей операционной системы (Windows, macOS или Linux).
	3.	Docker Compose уже включен в Docker Desktop.

Какие порты вам понадобятся?

	•	8080: Веб-приложение через Nginx (http://localhost:8080).
	•	5432: PostgreSQL (используется внутри контейнеров).

Вы можете изменить порты в файле docker-compose.yaml, если нужно.

Как запустить проект?

	1.	Убедитесь, что Docker Desktop запущен.
	2.	В терминале перейдите в корневую директорию проекта.
	3.  Запустите контейнеры:

```
docker-compose up -d
```

	4.	Установите зависимости Composer:

```
docker-compose exec php bash -c "cd /var/www/html/app && composer install"
```

	5.	И выполните миграции:

```
docker-compose exec php bash -c "cd /var/www/html/app && php bin/console doctrine:migrations:migrate --no-interaction"
```

	6.	Откройте http://localhost:8080 в браузере.

Проект готов к работе, если в БД есть тесты для прохождения, будет выведен их список.


## Импорт тестов

Для импорта тестов используется команда:

```
php bin/console app:load-questions questions.txt
```

Как выполнить команду в контейнере?

	1.	Запустите контейнеры:

```
docker-compose up –build
```

	2.	В командной строке выполните:

```
docker-compose exec php php bin/console app:load-questions questions.txt
```

    Файл с вопросами из задания можно скорировать из корня:

```
cp questions.txt ./app
```

## Авторизация пользователей

Полноценная авторизация в этом проекте не реализована. Однако для удобства добавлена возможность добавлять пользователей без пароля. Пользователи сохраняются в базе данных и привязываются к результатам тестов.

Как добавить пользователя?

	1.	При запуске теста, если пользователя с введенным email нет в базе данных, он автоматически создается.
	2.	Пользователь сохраняется в базе данных и привязывается к результатам тестов.
	3.	Это позволяет легко сохранять и отслеживать результаты тестов для каждого пользователя.


## Как хранятся результаты теста

Результаты теста сохраняются в таблицах TestResult и TestAnswer. Когда пользователь завершает тест, создается запись в таблице TestResult, связывающая пользователя с результатами теста. Каждая запись в TestResult имеет связанный список ответов, хранящихся в TestAnswer.

Описание битмасок

Битмаски используются для хранения и проверки ответов на вопросы теста. Каждому возможному ответу на вопрос назначается бит в битмаске. Например:

	•	Вопрос имеет 4 ответа: A, B, C, D.
	•	Правильные ответы: A и C.
	•	Битмаска правильных ответов: 0101 (5 в десятичной системе).

Пример проверки валидности ответа:

	•	Если пользователь выбрал A и C, их битмаска также будет 0101.
	•	Проверка правильности: побитовое И (AND) между битмаской правильных ответов и битмаской пользовательских ответов.

```
$isCorrect = ($userAnswerMask & $correctKey) === $correctKey;
```
## Структура базы данных

### Схема базы данных
```
+----------------+       +------------------+       
|      User      |       |     TestResult   |       
+----------------+       +------------------+       
| id             |<----->| id               |       
| name           |       | user_id          |       
| email          |       | created_at       |       
| created_at     |       | test_id          |<-------------------------------+
+----------------+       +------------------+                               |
                                 |                                          |
                                 |                                          |
                                 v                                          |
                         +------------------+                               |
                         |   TestAnswer     |                               |
                         +------------------+                               |
                         | id               |                               |
                         | test_result_id   |                               |
                         | question_id      |                               |
                         | answers          |                               |
                         | created_at       |                               |
                         +------------------+                               |
                                 |                                          |
                                 |                                          |
                                 v                                          |
                         +------------------+                               |
                         |    Question      |                               |
                         +------------------+                               |
                         | id               |                               |
                         | text             |                               |
                         | key              |                               |
                         | created_at       |                               |
                         | test_id          |<-----------------------------+
                         +------------------+
                                 |
                                 |
                                 v
                         +------------------+
                         |      Test        |
                         +------------------+
                         | id               |
                         | name             |
                         +------------------+
                                 |
                                 |
                                 v
                         +------------------+
                         |     Answer       |
                         +------------------+
                         | id               |
                         | question_id      |
                         | text             |
                         | bit              |
                         | created_at       |
                         +------------------+
```

## Исходные данные
Задание скопировано в input.pdf