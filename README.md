# Парсинг товаров Ozon

## Стек

- PHP (Symfony 7)
- Selenium (ChromeDriver)
- Docker

---

## Установка

1. Клонируем репозиторий:

```bash
git clone https://github.com/tinkyrain/ozon-parser.git
cd ozon-parser
```

2. Разворачивание проекта

```bash
composer install
make build
make up
```

3. Парсинг товара
```bash
make parse <sku>
```

