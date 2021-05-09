# Бандл парсинга Инстаграма посредством Instagram RapidAPI

Решение частной задачи, показавшее некоторую степень универсальности. 

Используется [api](https://rapidapi.com/ru/restyler/api/instagram40). В качестве дополнения
приложен механизм генерации фикстур с помощью консольных команд.

## Зачем?

Не захотел городить конструкцию с регистрацией приложения в Facebook и т.д. "Обычные" средства парсинга с некоторых,
 уже давних, пор работают с перебоями. Пришлось так.

## Установка

composer.json:

```json
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/proklung/instagram.parser.bundle"
        }
    ]
```

```bash

composer require proklung/instagram-parser-bundle

```

## Пример конфигурации бандла

```yaml
instagram_parser_rapid_api:
  defaults:
    enabled: true

  #####################################
  # Настройки кэширования. Опционально
  #####################################
  cache_path: 'cache/s1/instagram-parser'
  cache_ttl: 86400
  cache_user_data_ttl: 31536000

  ##############
  # Мок запроса
  ##############
  mock: false
  # Путь к фикстуре запроса картинок.
  fixture_response_path: '/local/classes/Bundles/InstagramParserRapidApiBundle/Fixture/response.txt'
  # Путь к фикстуре запроса данных пользователя.
  fixture_user_path: '/local/classes/Bundles/InstagramParserRapidApiBundle/Fixture/user.txt'

  ###########################
  # Информация о пользователе
  ###########################

  # См. https://codeofaninja.com/tools/find-instagram-user-id/
  # Или запрос вида https://www.instagram.com/web/search/topsearch/?query=dertaglu (поле PK)
  # Или получение через UserInfoRetriever.
  instagram_user_id: 8440733461

  # Имя пользователя.
  instagram_user_name: user_instagra,

  ################
  # Параметры API
  ################

  # Ключ к https://rapidapi.com/restyler/api/instagram40
  rapid_api_key: 5466bdf907vvv430e9579da6a738p18854cjsnb789fb399aa0
```

## Консольная команда

```
php bin/console make:instagram-fixtures exampleInstagramUserName
```
 
В результате положит две фикстуры (`user.txt` и `response.txt`) по адресам, указанным 
в ключах `fixture_user_path` и `fixture_response_path` бандла. 