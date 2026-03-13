## ⚡ Veloce Framework
**Veloce** (итал. *быстрый*) — это легковесный и высокопроизводительный PHP-фреймворк, построенный на принципах **AOT-компиляции** (Ahead-of-Time) и строгого соблюдения современных архитектурных стандартов.

### ✨ Ключевые особенности*   **🚀 Ultra-fast Routing**: Маршруты компилируются в оптимизированные PHP-массивы. Никакого парсинга регулярок в рантайме — только прямой поиск по готовым картам.
*   **🏗️ DI-Container**: Полноценный контейнер с поддержкой **Auto-wiring** (через Reflection API) и возможностью привязки интерфейсов к реализациям (`bind`).*   **🛡️ Security First**: Встроенная защита от CSRF-атак, шифрование на базе **AES-128-CBC + HMAC** и строгая валидация входящих данных.
*   **🧩 Immutable Objects**: Использование `readonly` классов для объектов-значений (Value Objects) и неизменяемых ответов (Response).*   **🎨 Lazy Rendering**: Изолированный рендеринг шаблонов через замыкания (Closures), который выполняется в самый последний момент перед отправкой.

### 🛠️ Быстрый старт1. Склонируйте репозиторий и установите автозагрузчик:
   ```bash
   composer install


   1. Инициализируйте окружение и скомпилируйте кэш маршрутов:
   
   php bin/veloce
   
   Команда создаст уникальную соль (salt.php) и скомпилирует карты маршрутов из application/rules.php.
   2. Настройте ваш веб-сервер (Nginx/Apache) на папку public/ как Document Root.

📖 Примеры кодаКонтроллер с внедрением зависимостей
Благодаря автовайрингу, вам не нужно вручную создавать экземпляры сервисов:

namespace Application\Controller;
use Veloce\Kernel\Controller;use Veloce\Database\DB;use Veloce\Kernel\Path;
class User extends Controller
{
    // DB будет внедрена автоматически из контейнера
    public function __construct(private DB $db) {}

    public function __invoke(Path $path): array
    {
        $user = $this->db->row("SELECT * FROM users WHERE id = ?", [$path->id]);
        
        return parent::render_template('user/index.php', [
            'user' => $user
        ]);
    }
}

Валидация форм в одно касание

public function update(): array
{
    $input = parent::request()->validate([
        'username' => 'required|min:3|max:20',
        'email'    => 'required|email'
    ]);

    if ($input->fails()) {
        return parent::render_template('user/edit.php', [
            'errors' => $input->errors()
        ]);
    }

    // Данные очищены и готовы к работе
    $name = $input->string('username');
}

📂 Структура проекта

* application/ — Логика вашего приложения (Контроллеры, Правила маршрутов).
* src/ — Ядро фреймворка (Veloce Engine).
* resource/cache/ — Скомпилированные карты, настройки и криптографическая соль.
* resource/views/ — Шаблоны оформления.
* public/ — Точка входа (index.php) и статические файлы (CSS/JS).

------------------------------
Veloce — спроектирован для тех, кто ценит чистоту кода и максимальную скорость отклика.
