# Сайт СТО — лендинг для привлечения клиентов

Одностраничный сайт автосервиса на Astro + Tailwind CSS. Статическая сборка, совместима с хостингом в Беларуси (hoster.by, hb.by, active.by).

## Возможности

- Адаптивный дизайн (mobile-first)
- Click-to-call телефоны
- Кнопки мессенджеров: Telegram, Viber, WhatsApp
- Яндекс.Карта с переходом в навигатор (Яндекс, Google, 2GIS)
- Форма «Перезвоните мне» → Telegram + email
- SEO: meta-теги, Open Graph, schema.org LocalBusiness
- Яндекс.Метрика (опционально)

## Быстрый старт

```bash
npm install
npm run dev
```

Сайт откроется на http://localhost:4321

## Настройка контента

Все данные сайта — в одном файле: [`src/data/site.ts`](src/data/site.ts)

Замените:
- название, слоган, город
- телефоны, email, мессенджеры
- адрес и координаты (lat/lon для карты)
- услуги, преимущества, отзывы
- ID Яндекс.Метрики (`yandexMetrikaId`)

Фото СТО положите в `public/images/` и подключите в компоненте Hero.

## Сборка

```bash
npm run build
```

Результат — папка `dist/` со всеми файлами для загрузки на хостинг.

## Деплой на hoster.by

### 1. Домен и хостинг

1. Зарегистрируйте домен `.by` или `.бел` на [hoster.by](https://hoster.by)
2. Подключите тариф «Лайт» или «Стандарт»
3. Включите SSL (Let's Encrypt — бесплатно в панели)

### 2. Загрузка файлов

1. Выполните `npm run build`
2. Загрузите **содержимое** папки `dist/` в корень сайта (`public_html/`) через FTP или файловый менеджер
3. Убедитесь, что `api/callback.php` загружен вместе с остальными файлами

### 3. Настройка формы обратного звонка

1. Создайте Telegram-бота через [@BotFather](https://t.me/BotFather)
2. Узнайте свой `chat_id` (напишите боту, затем откройте `https://api.telegram.org/bot<TOKEN>/getUpdates`)
3. На сервере скопируйте `api/config.example.php` → `api/config.php`
4. Заполните токен, chat_id и email в `config.php`

```php
return [
    'telegram_bot_token' => '123456:ABC...',
    'telegram_chat_id'   => '123456789',
    'email_to'           => 'info@yourdomain.by',
    'email_from'         => 'noreply@yourdomain.by',
    ...
];
```

### 4. URL сайта (важно для ссылки и SEO)

1. Скопируйте `.env.example` → `.env`
2. Укажите домен: `SITE=https://ваш-домен.by`
3. То же значение пропишите в `siteUrl` в [`src/data/site.ts`](src/data/site.ts)
4. Соберите сайт заново: `npm run build`

Без публичного домена ссылку **нельзя** отправить другому человеку — `localhost` виден только на вашем компьютере.

## Как отправить ссылку клиенту

1. Опубликуйте сайт на hoster.by (см. выше).
2. Откройте в браузере `https://ваш-домен.by` — должна открыться главная.
3. Эту ссылку можно отправлять в Telegram, Viber, SMS, email.

В мессенджерах подтянется **карточка превью** (заголовок, описание, картинка) — для этого уже настроены Open Graph-теги.

## Продвижение в поиске (Яндекс / Google)

Мета-теги на сайте уже подготовлены (`title`, `description`, `keywords`, schema.org, `sitemap.xml`, `robots.txt`). Это **не гарантирует** первые строки сразу, но даёт поисковикам правильные данные.

После публикации сделайте:

1. **Яндекс.Вебмастер** — [webmaster.yandex.ru](https://webmaster.yandex.ru): добавьте сайт, подтвердите (код в `site.seo.yandexVerification` в `site.ts`).
2. **Google Search Console** — [search.google.com/search-console](https://search.google.com/search-console): добавьте сайт, код в `site.seo.googleVerification`.
3. **Яндекс.Бизнес** и **Google Business** — карточка организации с адресом Бехтерева 8к6, телефоном и часами работы (сильно влияет на локальный поиск «СТО рядом»).
4. **Яндекс.Метрика** — ID в `yandexMetrikaId` в `site.ts`.

Для лучшего превью в мессенджерах добавьте фото `public/og-image.jpg` (1200×630 px) и укажите путь в `site.seo.ogImage`.

## Структура проекта

```
src/
  components/   — секции лендинга
  data/site.ts  — контент сайта
  layouts/      — базовый layout с SEO
  pages/        — index.astro
public/
  api/          — PHP-обработчик формы
  images/       — фото СТО
```

## Стоимость содержания (~в год)

| Статья | ~Стоимость |
|--------|-----------|
| Домен `.by` | 42 BYN |
| Хостинг «Лайт» | 50–70 BYN |
| SSL, Telegram | бесплатно |
