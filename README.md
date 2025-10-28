# Symfony Docker

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework,
with [FrankenPHP](https://frankenphp.dev) and [Caddy](https://caddyserver.com/) inside!

![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

Задание

Представляем вашему вниманию небольшое тестовое задание.
Цель
Реализовать сервис на Symfony (рекомендуется) или Laravel, работающий с данными из
вселенной Rick and Morty: персонажи, эпизоды и отзывы.
Сущности
● Персонаж (имя, пол, статус (жив, мёртв, неизвестно), URL).
● Эпизоды (название, дата выхода, сезон, серия, отзывы).
● Отзывы (автор, текст, дата публикации, рейтинг).
● Персонажи участвуют в эпизодах. Эпизод имеет множество отзывов.
Функциональные требования
1. Импорт и актуализация данных
a. Реализовать регулярную загрузку данных о персонажах и эпизодах из
внешнего API (без их библиотеки).
b. При первом запуске загрузить к каждому эпизоду от 50 до 500 отзывов из
JSON-файла.
c. Авторы отзывов генерируются с помощью Faker.
2. При добавлении отзыва рейтинг автоматически определяется в диапазоне от 1.0 до
5.0 одним из двух способов (текущий способ должен задаваться через
конфигурацию):
a. либо по полю compound из ответа библиотеки php-sentiment-analyzer.
b. либо добавляется случайный.
3. Реализовать API для добавления нового отзыва к конкретному эпизоду (указываются
текст и автор).
4. Реализовать API для получения эпизодов, их отзывов и участвующих персонажей.
a. Возможность фильтрации по персонажам, сезону и дате выхода.
b. Возможность сортировки по дате выхода или по средней оценке из отзывов.
c. Предусмотреть пагинацию.
5. Доп.: желательно завернуть всё в Docker

