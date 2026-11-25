<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\MailTemplate;
use App\Models\NavItem;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SiteText;
use App\Models\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Initial content for the site. Facts come from the CVs and the previous
 * fernandovasquez.tech; copy was rewritten for this version.
 *
 * Safe to run again: existing records are left as edited in the panel and
 * only missing ones are created.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->media();
        $this->settings();
        $this->texts();
        $this->services();
        $this->technologies();
        $this->navigation();
        $this->links();
        $this->mailTemplates();
    }

    private function media(): void
    {
        $assets = database_path('seeders/assets');
        $public = Storage::disk('public');
        if (! $public->exists('media/memoji.png')) {
            $public->put('media/memoji.png', file_get_contents("{$assets}/memoji.png"));
        }
        if (! $public->exists('media/fernando-vasquez.png')) {
            $public->put('media/fernando-vasquez.png', file_get_contents("{$assets}/fernando-vasquez.png"));
        }
        foreach (glob("{$assets}/logos/*.{svg,png}", GLOB_BRACE) as $file) {
            $path = 'logos/'.basename($file);
            if (! $public->exists($path)) {
                $public->put($path, file_get_contents($file));
            }
        }
        foreach (['es', 'en'] as $locale) {
            $path = "cv/cv_fvasquez_{$locale}.pdf";
            if (! Storage::disk('local')->exists($path)) {
                Storage::disk('local')->put($path, file_get_contents("{$assets}/cv_fvasquez_{$locale}.pdf"));
            }
        }
    }

    private function settings(): void
    {
        $defaults = [
            'profile.name' => 'Fernando Vásquez',
            'profile.photo' => 'media/fernando-vasquez.png',
            'profile.memoji' => 'media/memoji.png',
            'profile.available' => true,
            'contact.email' => 'hola@fernandovasquez.tech',
            'cv.es' => 'cv/cv_fvasquez_es.pdf',
            'cv.en' => 'cv/cv_fvasquez_en.pdf',
            'seo.title.es' => 'Fernando Vásquez · Desarrollador full-stack y diseñador UX/UI',
            'seo.title.en' => 'Fernando Vásquez · Full-stack developer and UX/UI designer',
            'seo.description.es' => 'Desarrollador full-stack y diseñador UX/UI en Guatemala. Sitios, aplicaciones web y móviles, infraestructura y automatización, desde 2016.',
            'seo.description.en' => 'Full-stack developer and UX/UI designer in Guatemala. Websites, web and mobile apps, infrastructure and automation, since 2016.',
            'recaptcha.enabled' => false,
            'recaptcha.version' => 'v3',
            'recaptcha.threshold' => 0.5,
            'recaptcha.action' => 'contact',
            'mail.enabled' => false,
            'mail.port' => 587,
            'mail.encryption' => 'tls',
            'mail.from_address' => 'hola@fernandovasquez.tech',
            'mail.from_name' => 'Fernando Vásquez',
            'mail.notify_to' => 'hola@fernandovasquez.tech',
        ];
        foreach ($defaults as $key => $value) {
            if (! Setting::query()->where('key', $key)->exists()) {
                Setting::put($key, $value);
            }
        }
    }

    private function texts(): void
    {
        // [key, label, es, en, multiline]
        $groups = [
            'navigation' => [
                ['a11y.skip', 'Enlace para saltar al contenido', 'Saltar al contenido', 'Skip to content'],
                ['a11y.home', 'Etiqueta del logo', 'Fernando Vásquez, ir al inicio', 'Fernando Vásquez, back to top'],
                ['nav.label', 'Nombre de la navegación principal', 'Navegación principal', 'Main navigation'],
                ['nav.menu', 'Botón de menú (móvil)', 'Menú', 'Menu'],
                ['nav.close', 'Botón para cerrar el menú', 'Cerrar', 'Close'],
                ['nav.language', 'Nombre del selector de idioma', 'Idioma', 'Language'],
                ['nav.lang_es', 'Nombre del español', 'Español', 'Spanish'],
                ['nav.lang_en', 'Nombre del inglés', 'Inglés', 'English'],
            ],
            'hero' => [
                ['hero.available', 'Cápsula de disponibilidad', 'Disponible para trabajar', 'Available for work'],
                ['hero.role', 'Cápsula superior', 'Desarrollador full-stack · Diseñador UX/UI', 'Full-stack developer · UX/UI designer'],
                ['hero.greeting', 'Saludo antes del nombre', 'Hola, soy', "Hi, I'm"],
                ['hero.expert', 'Línea 2: texto antes de la especialidad', 'experto en', 'an expert in'],
                ['hero.skill_a', 'Línea 2: cápsula de la primera especialidad', 'Desarrollo Fullstack', 'Fullstack Development'],
                ['hero.and', 'Línea 2: texto entre especialidades', 'y', 'and'],
                ['hero.skill_b', 'Línea 2: cápsula de la segunda especialidad', 'Diseño UX-UI', 'UX-UI Design'],
                ['hero.tagline', 'Línea 3', 'combinando ambas skills de inicio a fin.', 'combining both skills from start to finish.'],
                ['hero.cta_primary', 'Botón principal', 'Hablemos', "Let's talk"],
                ['hero.cta_secondary', 'Botón secundario', 'Descargar CV', 'Download Resume'],
                ['hero.stat_projects_value', 'Dato 1: cifra', '+200', '+200'],
                ['hero.stat_projects_label', 'Dato 1: texto', 'proyectos', 'projects'],
                ['hero.stat_countries_value', 'Dato 2: cifra', '+5', '+5'],
                ['hero.stat_countries_label', 'Dato 2: texto', 'países', 'countries'],
                ['hero.since', 'Dato: año de inicio', 'En la web desde 2016', 'Building for the web since 2016'],
                ['hero.location', 'Dato: ubicación', 'Guatemala · Remoto para cualquier país', 'Guatemala · Remote work available in any country'],
                ['hero.photo_alt', 'Texto alternativo de la foto', 'Retrato de Fernando Vásquez', 'Portrait of Fernando Vásquez'],
                ['hero.memoji_alt', 'Texto alternativo del memoji', 'Memoji de Fernando Vásquez saludando', 'Memoji of Fernando Vásquez waving'],
                ['hero.alias', 'Alias', 'También conocido como Luisfer', 'Also known as Luisfer'],
            ],
            'profile' => [
                ['profile.kicker', 'Etiqueta de sección', 'Perfil', 'Profile'],
                ['profile.title', 'Título', 'Diseño y código en el mismo proceso.', 'Design and code in the same process.'],
                ['profile.body', 'Párrafo 1', 'Soy Ingeniero en Sistemas con más de diez años de experiencia en proyectos digitales, con un enfoque en desarrollo de aplicaciones móviles, páginas y aplicaciones web y el diseño UX/UI. En cada proyecto combino mi formación técnica con mi criterio de diseño para crear soluciones funcionales, intuitivas y alineadas con las necesidades de las personas que las utilizan.', 'I am a Systems Engineer with more than ten years of experience in digital projects, specializing in mobile app development, websites, and web applications, as well as UX/UI design. In every project, I combine my technical training with my design expertise to create functional, intuitive solutions that are tailored to the needs of the people who use them.', true],
                ['profile.body_b', 'Párrafo 2', 'Como diseño lo que desarrollo, las decisiones de interfaz llegan intactas a producción y las restricciones técnicas se consideran desde el primer boceto.', 'Because I design what I build, interface decisions reach production intact and technical constraints are considered from the first sketch.', true],
                ['profile.body_c', 'Párrafo 3', 'Fuera del ámbito profesional, disfruto del café, los cómics, las películas y los videojuegos.', 'Outside of work, I enjoy coffee, comics, movies and video games.', true],
            ],
            'services' => [
                ['services.kicker', 'Etiqueta de sección', 'Servicios', 'Services'],
                ['services.title', 'Título', 'Lo que hago, de la idea al mantenimiento.', 'What I do, from idea to maintenance.'],
                ['services.lead', 'Introducción', 'Puedo encargarme de una parte o de todo el proceso. Elige un servicio para consultarlo directamente.', 'I can take on one part or the whole process. Pick a service to ask about it directly.', true],
                ['services.includes', 'Título de la lista de cada tarjeta', 'Incluye', 'Includes'],
                ['services.ask', 'Botón de cada tarjeta', 'Consultar este servicio', 'Ask about this service'],
            ],
            'technologies' => [
                ['tech.kicker', 'Etiqueta de sección', 'Tecnologías', 'Technologies'],
                ['tech.title', 'Título', 'Herramientas con las que trabajo.', 'Tools I work with.'],
                ['tech.lead', 'Introducción', 'Del diseño a la automatización: las herramientas que uso hoy en proyectos reales.', 'From design to automation: the tools I use today on real projects.', true],
                ['tech.cat.design', 'Categoría: diseño', 'Diseño', 'Design'],
                ['tech.cat.frontend', 'Categoría: frontend', 'Frontend', 'Frontend'],
                ['tech.cat.backend', 'Categoría: backend', 'Backend', 'Backend'],
                ['tech.cat.data', 'Categoría: datos', 'Datos', 'Data'],
                ['tech.cat.mobile', 'Categoría: aplicaciones móviles', 'Aplicaciones móviles', 'Mobile applications'],
                ['tech.cat.infrastructure', 'Categoría: infraestructura', 'Infraestructura y flujo de trabajo', 'Infrastructure and workflow'],
                ['tech.cat.automation', 'Categoría: automatización e IA', 'Automatización e IA', 'Automation and AI'],
            ],
            'cv' => [
                ['cv.kicker', 'Etiqueta de sección', 'CV', 'CV'],
                ['cv.title', 'Título', 'Mi carrera, en dos idiomas', 'My Career, in Two Languages'],
                ['cv.lead', 'Introducción', 'La trayectoria completa, la formación y las habilidades en un PDF listo para descargar.', 'Full experience, education and skills in a PDF ready to download.', true],
                ['cv.es', 'Nombre del CV en español', 'CV en español', 'CV in Spanish'],
                ['cv.en', 'Nombre del CV en inglés', 'CV en inglés', 'CV in English'],
                ['cv.download', 'Botón de descarga', 'Descargar PDF', 'Download PDF'],
                ['cv.updated', 'Texto antes de la fecha', 'Actualizado', 'Updated'],
            ],
            'contact' => [
                ['contact.kicker', 'Etiqueta de sección', 'Contacto', 'Contact'],
                ['contact.title', 'Título', 'Hablemos de tu proyecto.', "Let's talk about your project."],
                ['contact.lead', 'Introducción', 'Cuéntame qué necesitas construir o mejorar, para cuándo y en qué etapa está. Leo y respondo cada mensaje personalmente.', 'Tell me what you need to build or improve, by when and where it stands today. I read and answer every message myself.', true],
                ['contact.email_label', 'Etiqueta del correo', 'Correo', 'Email'],
                ['contact.location_label', 'Etiqueta de la ubicación', 'Ubicación', 'Location'],
                ['form.name', 'Campo: nombre', 'Nombre', 'Name'],
                ['form.email', 'Campo: correo', 'Correo electrónico', 'Email'],
                ['form.company', 'Campo: empresa', 'Empresa', 'Company'],
                ['form.phone', 'Campo: teléfono', 'Teléfono', 'Phone'],
                ['form.optional', 'Marca de campo opcional', 'opcional', 'optional'],
                ['form.service', 'Campo: servicio', 'Servicio', 'Service'],
                ['form.service_placeholder', 'Opción vacía del servicio', 'Selecciona un servicio', 'Choose a service'],
                ['form.service_other', 'Opción "otro"', 'Otro o aún no lo sé', "Something else, or I'm not sure yet"],
                ['form.message', 'Campo: mensaje', 'Mensaje', 'Message'],
                ['form.message_hint', 'Ayuda del mensaje', 'Qué necesitas, para cuándo y cualquier contexto útil.', 'What you need, by when, and any useful context.'],
                ['form.submit', 'Botón de envío', 'Enviar mensaje', 'Send message'],
                ['form.sending', 'Botón mientras envía', 'Enviando…', 'Sending…'],
                ['form.errors_summary', 'Aviso de errores', 'Revisa los campos marcados para poder enviar el mensaje.', 'Check the highlighted fields to send your message.'],
                ['form.success_title', 'Título de éxito', 'Mensaje recibido', 'Message received'],
                ['form.success_body', 'Texto de éxito ({name} disponible)', 'Gracias, {name}. Te envié una confirmación a tu correo y te responderé pronto.', "Thanks, {name}. I've sent a confirmation to your inbox and will get back to you soon.", true],
                ['form.send_another', 'Botón para enviar otro', 'Enviar otro mensaje', 'Send another message'],
                ['form.recaptcha_notice', 'Aviso legal de reCAPTCHA', 'Este formulario está protegido por reCAPTCHA. Aplican la Política de privacidad y las Condiciones del servicio de Google.', "This form is protected by reCAPTCHA. Google's Privacy Policy and Terms of Service apply.", true],
                ['errors.name', 'Error: nombre', 'Escribe tu nombre.', 'Enter your name.'],
                ['errors.email', 'Error: correo', 'Escribe un correo válido, por ejemplo nombre@empresa.com.', 'Enter a valid email, for example name@company.com.'],
                ['errors.service', 'Error: servicio', 'Elige el servicio que te interesa.', 'Choose the service you are interested in.'],
                ['errors.message', 'Error: mensaje', 'Cuéntame un poco más (mínimo 20 caracteres).', 'Tell me a little more (at least 20 characters).'],
                ['errors.too_long', 'Error: texto demasiado largo', 'Este campo es demasiado largo.', 'This field is too long.'],
                ['errors.phone', 'Error: teléfono', 'Escribe un teléfono de al menos 6 dígitos, usando solo números.', 'Enter a phone number with at least 6 digits, using numbers only.'],
                ['errors.recaptcha', 'Error: reCAPTCHA', 'No pudimos verificar que el envío lo hace una persona. Recarga la página e inténtalo de nuevo.', "We couldn't verify this was sent by a person. Reload the page and try again.", true],
                ['errors.recaptcha_checkbox', 'Error: casilla de reCAPTCHA sin marcar', 'Marca la casilla «No soy un robot» y vuelve a enviar.', 'Tick the "I\'m not a robot" box and send again.'],
                ['errors.rate', 'Error: demasiados intentos', 'Recibí varios mensajes seguidos desde tu conexión. Espera unos minutos e inténtalo de nuevo.', 'Several messages came from your connection in a short time. Wait a few minutes and try again.', true],
                ['errors.generic', 'Error inesperado', 'No se pudo enviar el mensaje. Inténtalo de nuevo o escríbeme directamente por correo.', "The message couldn't be sent. Try again or email me directly.", true],
            ],
            'footer' => [
                ['footer.rights', 'Texto legal', 'Todos los derechos reservados.', 'All rights reserved.'],
                ['footer.top', 'Enlace para volver arriba', 'Volver arriba', 'Back to top'],
            ],
        ];

        // Keys from earlier versions of the hero.
        SiteText::query()->whereIn('key', ['hero.line_a', 'hero.line_b', 'hero.line_c', 'hero.lead', 'hero.specialty', 'hero.from', 'hero.country', 'profile.work', 'profile.education', 'profile.present'])->delete();

        $sort = 0;
        foreach ($groups as $group => $rows) {
            foreach ($rows as $row) {
                [$key, $label, $es, $en] = $row;
                SiteText::query()->firstOrCreate(['key' => $key], [
                    'group' => $group,
                    'label' => $label,
                    'value' => ['es' => $es, 'en' => $en],
                    'multiline' => $row[4] ?? false,
                    'sort' => $sort++,
                ]);
            }
        }
    }

    private function services(): void
    {
        $rows = [
            ['fullstack', 'code-xml',
                ['Desarrollo full-stack', 'Full-stack development'],
                ['Aplicaciones web completas: modelo de datos, lógica de negocio, APIs e interfaz, construidas con Laravel, Vue.js y Node.js.', 'Complete web applications: data model, business logic, APIs and interface, built with Laravel, Vue.js and Node.js.'],
                [['Aplicaciones web a la medida', 'Paneles y sistemas internos', 'APIs e integraciones entre sistemas', 'Arquitectura, seguridad y rendimiento'],
                    ['Custom web applications', 'Dashboards and internal systems', 'APIs and system integrations', 'Architecture, security and performance']]],
            ['ux-ui', 'pen-tool',
                ['Diseño UX/UI', 'UX/UI design'],
                ['Interfaces pensadas para usarse y para construirse: wireframes, prototipos y sistemas visuales listos para desarrollo.', 'Interfaces designed to be used and to be built: wireframes, prototypes and visual systems ready for development.'],
                [['Wireframes y prototipos', 'Mapas de experiencia', 'Identidad y elementos visuales', 'Diseño de interacción y responsive'],
                    ['Wireframes and prototypes', 'Experience mapping', 'Visual identity and assets', 'Interaction and responsive design']]],
            ['web-mobile', 'smartphone',
                ['Sitios web y apps móviles', 'Websites and mobile apps'],
                ['Sitios institucionales, publicaciones digitales y aplicaciones móviles, del diseño a la publicación.', 'Institutional sites, digital publications and mobile apps, from design to launch.'],
                [['Sitios corporativos e institucionales', 'Revistas y publicaciones digitales', 'Aplicaciones móviles', 'Optimización y adaptación a cualquier pantalla'],
                    ['Corporate and institutional websites', 'Digital magazines and publications', 'Mobile apps', 'Optimization for every screen size']]],
            ['infrastructure', 'server',
                ['Infraestructura y mantenimiento', 'Infrastructure and maintenance'],
                ['El entorno donde vive tu proyecto: alojamiento, dominios, correo y certificados, con monitoreo y respaldos para que siga funcionando.', 'Where your project lives: hosting, domains, email and certificates, with monitoring and backups so it keeps running.'],
                [['Alojamiento y despliegue en AWS y Linux', 'Dominios, correo y certificados SSL', 'Monitoreo y respaldos', 'Webmaster y mantenimiento continuo'],
                    ['Hosting and deployment on AWS and Linux', 'Domains, email and SSL certificates', 'Monitoring and backups', 'Webmaster and ongoing maintenance']]],
            ['automation', 'workflow',
                ['Automatización, integraciones e IA', 'Automation, integrations and AI'],
                ['Flujos que conectan tus herramientas y reducen trabajo manual, con n8n y Zapier, chatbots en AtomChat.io y modelos como ChatGPT y Claude.', 'Workflows that connect your tools and cut manual work, using n8n and Zapier, AtomChat.io chatbots and models such as ChatGPT and Claude.'],
                [['Automatización de procesos con n8n y Zapier', 'Integraciones entre aplicaciones y APIs', 'Chatbots y asistentes conversacionales', 'Agentes de IA para tareas concretas'],
                    ['Process automation with n8n and Zapier', 'Integrations between apps and APIs', 'Chatbots and conversational assistants', 'AI agents for well-defined tasks']]],
        ];
        foreach ($rows as $i => [$slug, $icon, $title, $summary, $items]) {
            Service::query()->firstOrCreate(['slug' => $slug], [
                'icon' => $icon,
                'title' => ['es' => $title[0], 'en' => $title[1]],
                'summary' => ['es' => $summary[0], 'en' => $summary[1]],
                'items' => ['es' => $items[0], 'en' => $items[1]],
                'sort' => $i,
            ]);
        }
    }

    private function technologies(): void
    {
        // [name, category, official colour logo (database/seeders/assets/logos), brand colour, url]
        // Logos: Devicon (MIT), Lobe Icons (MIT), Simple Icons (Zapier) and AtomChat.io's own icon.
        $rows = [
            ['Figma', 'design', 'figma.svg', '#F24E1E', 'https://www.figma.com'],
            ['Sketch', 'design', 'sketch.svg', '#F7B500', 'https://www.sketch.com'],
            ['Adobe XD', 'design', 'adobe-xd.svg', '#FF61F6', 'https://www.adobe.com/products/xd.html'],
            ['Adobe Photoshop', 'design', 'photoshop.svg', '#31A8FF', 'https://www.adobe.com/products/photoshop.html'],
            ['Adobe Illustrator', 'design', 'illustrator.svg', '#FF9A00', 'https://www.adobe.com/products/illustrator.html'],
            ['HTML', 'frontend', 'html5.svg', '#E34F26', null],
            ['CSS', 'frontend', 'css3.svg', '#1572B6', null],
            ['JavaScript', 'frontend', 'javascript.svg', '#F7DF1E', null],
            ['Vue.js', 'frontend', 'vuejs.svg', '#4FC08D', 'https://vuejs.org'],
            ['React', 'frontend', 'react.svg', '#61DAFB', 'https://react.dev'],
            ['Tailwind CSS', 'frontend', 'tailwindcss.svg', '#06B6D4', 'https://tailwindcss.com'],
            ['Bootstrap', 'frontend', 'bootstrap.svg', '#7952B3', 'https://getbootstrap.com'],
            ['jQuery', 'frontend', 'jquery.svg', '#0769AD', 'https://jquery.com'],
            ['PHP', 'backend', 'php.svg', '#777BB4', 'https://www.php.net'],
            ['Laravel', 'backend', 'laravel.svg', '#FF2D20', 'https://laravel.com'],
            ['CodeIgniter', 'backend', 'codeigniter.svg', '#EF4223', 'https://codeigniter.com'],
            ['Node.js', 'backend', 'nodejs.svg', '#5FA04E', 'https://nodejs.org'],
            ['MySQL', 'data', 'mysql.svg', '#4479A1', 'https://www.mysql.com'],
            ['Oracle Database', 'data', 'oracle.svg', '#EA1B22', 'https://www.oracle.com/database/'],
            ['MariaDB', 'data', 'mariadb.svg', '#003545', 'https://mariadb.org'],
            ['MongoDB', 'data', 'mongodb.svg', '#00ED64', 'https://www.mongodb.com'],
            ['Firebase', 'data', 'firebase.svg', '#FFCA28', 'https://firebase.google.com'],
            ['iOS', 'mobile', 'apple.svg', '#000000', 'https://developer.apple.com/ios/'],
            ['Android', 'mobile', 'android.svg', '#3DDC84', 'https://www.android.com'],
            ['AWS', 'infrastructure', 'aws.svg', '#FF9900', 'https://aws.amazon.com'],
            ['Linux', 'infrastructure', 'linux.svg', '#FCC624', null],
            ['Git', 'infrastructure', 'git.svg', '#F03C2E', 'https://git-scm.com'],
            ['GitHub', 'infrastructure', 'github.svg', '#181717', 'https://github.com'],
            ['GitLab', 'infrastructure', 'gitlab.svg', '#FC6D26', 'https://gitlab.com'],
            ['Postman', 'infrastructure', 'postman.svg', '#FF6C37', 'https://www.postman.com'],
            ['n8n', 'automation', 'n8n.svg', '#EA4B71', 'https://n8n.io'],
            ['Zapier', 'automation', 'zapier.svg', '#FF4F00', 'https://zapier.com'],
            ['AtomChat.io', 'automation', 'atomchat.png', '#FF6B00', 'https://atomchat.io'],
            ['ChatGPT', 'automation', 'chatgpt.svg', '#000000', 'https://chatgpt.com'],
            ['Claude Code', 'automation', 'claude-code.svg', '#D97757', 'https://claude.com/product/claude-code'],
        ];
        foreach ($rows as $i => [$name, $category, $logo, $color, $url]) {
            Technology::query()->firstOrCreate(['name' => $name], [
                'category' => $category,
                'logo_path' => $logo ? "logos/{$logo}" : null,
                'brand_color' => $color,
                'url' => $url,
                'sort' => $i,
            ]);
        }
    }

    private function navigation(): void
    {
        $rows = [
            ['profile', 'Perfil', 'Profile'],
            ['services', 'Servicios', 'Services'],
            ['technologies', 'Tecnologías', 'Technologies'],
            ['cv', 'CV', 'CV'],
            ['contact', 'Contacto', 'Contact'],
        ];
        foreach ($rows as $i => [$target, $es, $en]) {
            NavItem::query()->firstOrCreate(['target' => $target], ['label' => ['es' => $es, 'en' => $en], 'sort' => $i]);
        }
    }

    private function links(): void
    {
        Link::query()->firstOrCreate(['url' => 'mailto:hola@fernandovasquez.tech'], [
            'label' => ['es' => 'Correo', 'en' => 'Email'],
            'icon' => 'mail',
            'sort' => 0,
        ]);
    }

    private function mailTemplates(): void
    {
        MailTemplate::query()->firstOrCreate(['key' => MailTemplate::VISITOR_ACK], [
            'name' => 'Acuse al visitante',
            'subject' => [
                'es' => 'Recibí tu mensaje, {name}',
                'en' => 'I got your message, {name}',
            ],
            'body' => [
                'es' => "Hola, {name}:\n\nGracias por escribirme. Recibí tu mensaje sobre {service} y te responderé personalmente lo antes posible.\n\nEsto es lo que me enviaste:\n\n{message}\n\nSaludos,\nFernando Vásquez\nfernandovasquez.tech",
                'en' => "Hi {name},\n\nThanks for reaching out. I received your message about {service} and will reply personally as soon as I can.\n\nHere is what you sent:\n\n{message}\n\nBest,\nFernando Vásquez\nfernandovasquez.tech",
            ],
        ]);

        MailTemplate::query()->firstOrCreate(['key' => MailTemplate::OWNER_NOTIFICATION], [
            'name' => 'Notificación interna',
            'subject' => [
                'es' => 'Nuevo contacto: {name} · {service}',
                'en' => 'New lead: {name} · {service}',
            ],
            'body' => [
                'es' => "Nuevo mensaje desde fernandovasquez.tech\n\nNombre: {name}\nCorreo: {email}\nEmpresa: {company}\nTeléfono: {phone}\nServicio: {service}\nFecha: {date}\n\nMensaje:\n{message}",
                'en' => "New message from fernandovasquez.tech\n\nName: {name}\nEmail: {email}\nCompany: {company}\nPhone: {phone}\nService: {service}\nDate: {date}\n\nMessage:\n{message}",
            ],
        ]);
    }
}
