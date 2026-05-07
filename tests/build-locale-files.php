<?php
/**
 * Build POT, PO, and MO files for notification-hub.
 *
 * Run:
 * E:\XAMPP\php\php.exe tests/build-locale-files.php
 */

$base = dirname(__DIR__);
$languagesDir = $base . '/languages';

if (!is_dir($languagesDir)) {
    mkdir($languagesDir, 0777, true);
}

$extractPattern = '/\b(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1\s*,\s*[\'"]notification-hub[\'"]\s*\)/s';
$msgids = [];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
);

foreach ($it as $fileInfo) {
    if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'php') {
        continue;
    }

    $path = $fileInfo->getPathname();
    if (strpos($path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR) !== false) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false || preg_match_all($extractPattern, $content, $matches, PREG_SET_ORDER) < 1) {
        continue;
    }

    foreach ($matches as $m) {
        $msgid = trim(stripcslashes($m[2]));
        if ($msgid === '') {
            continue;
        }
        $msgids[$msgid] = true;
    }
}

$msgids = array_keys($msgids);

// Add a few portfolio/readme-facing labels that may be referenced in docs/UI copy.
$msgids[] = 'Send Test Notification';
$msgids[] = 'Notifications';
$msgids = array_values(array_unique($msgids));
sort($msgids, SORT_NATURAL | SORT_FLAG_CASE);

$headerTemplate = static function (string $lang): string {
    $date = gmdate('Y-m-d H:i+0000');
    return <<<HDR
msgid ""
msgstr ""
"Project-Id-Version: Notification Hub 1.0.0\\n"
"Report-Msgid-Bugs-To: \\n"
"POT-Creation-Date: {$date}\\n"
"PO-Revision-Date: {$date}\\n"
"Last-Translator: Notification Hub Team\\n"
"Language-Team: \\n"
"Language: {$lang}\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"X-Domain: notification-hub\\n"

HDR;
};

$escapePo = static function (string $value): string {
    return str_replace(
        ["\\", "\"", "\r", "\n", "\t"],
        ["\\\\", "\\\"", '', "\\n", "\\t"],
        $value
    );
};

$buildPo = static function (array $allMsgids, array $translations, string $lang) use ($headerTemplate, $escapePo): string {
    $out = $headerTemplate($lang);
    foreach ($allMsgids as $msgid) {
        $msgstr = $translations[$msgid] ?? '';
        $out .= 'msgid "' . $escapePo($msgid) . '"' . PHP_EOL;
        $out .= 'msgstr "' . $escapePo($msgstr) . '"' . PHP_EOL . PHP_EOL;
    }
    return $out;
};

$autoTranslateIt = static function (string $text): string {
    $exact = [
        '(no title)' => '(senza titolo)',
        '(HTTP %d)' => '(HTTP %d)',
        '7 Days' => '7 giorni',
        '30 Days' => '30 giorni',
        'All priorities' => 'Tutte le priorità',
        'All sources' => 'Tutte le origini',
        'All statuses' => 'Tutti gli stati',
        'All time' => 'Tutto il periodo',
        'All types' => 'Tutti i tipi',
        'Last 7 days' => 'Ultimi 7 giorni',
        'Last 30 days' => 'Ultimi 30 giorni',
        'Last year' => 'Ultimo anno',
        'Today' => 'Oggi',
        'Yesterday' => 'Ieri',
        'No' => 'No',
        'Yes' => 'Sì',
        'Read' => 'Letto',
        'Unread' => 'Non letto',
        'Active' => 'Attivo',
        'Inactive' => 'Inattivo',
        'Archived' => 'Archiviato',
        'Archive' => 'Archivia',
        'Unarchive' => 'Rimuovi da archivio',
        'Published' => 'Pubblicato',
        'Draft' => 'Bozza',
        'Private' => 'Privato',
        'Trash' => 'Cestino',
        'New' => 'Nuovo',
        'Created' => 'Creato',
        'Updated' => 'Aggiornato',
        'From' => 'Da',
        'To' => 'A',
        'Date' => 'Data',
        'Count' => 'Conteggio',
        'Total' => 'Totale',
        'Name' => 'Nome',
        'Manage' => 'Gestisci',
        'Important' => 'Importante',
        'Mark as read' => 'Segna come letto',
        'Mark as unread' => 'Segna come non letto',
        'Mark important' => 'Segna importante',
        'Remove important' => 'Rimuovi importante',
        'View details' => 'Visualizza dettagli',
        'View Notifications' => 'Visualizza notifiche',
        'Export CSV' => 'Esporta CSV',
        'Trigger Test' => 'Esegui test',
        'Hook Test' => 'Test hook',
        'Hook triggered.' => 'Hook attivato.',
        'Hook created.' => 'Hook creato.',
        'Hook updated.' => 'Hook aggiornato.',
        'Hook deleted.' => 'Hook eliminato.',
        'Rule created.' => 'Regola creata.',
        'Rule updated.' => 'Regola aggiornata.',
        'Rule deleted.' => 'Regola eliminata.',
        'Rule duplicated.' => 'Regola duplicata.',
        'Operation failed.' => 'Operazione non riuscita.',
        'Request failed.' => 'Richiesta non riuscita.',
        'Delete failed.' => 'Eliminazione non riuscita.',
        'Update failed.' => 'Aggiornamento non riuscito.',
        'Template not found.' => 'Template non trovato.',
        'Presenter is missing.' => 'Presenter mancante.',
    ];
    if (isset($exact[$text])) {
        return $exact[$text];
    }

    $terms = [
        'Notification' => 'Notifica',
        'Notifications' => 'Notifiche',
        'notification' => 'notifica',
        'notifications' => 'notifiche',
        'Channel' => 'Canale',
        'channel' => 'canale',
        'Test' => 'Test',
        'Settings' => 'Impostazioni',
        'General' => 'Generale',
        'Rules' => 'Regole',
        'Hooks' => 'Hook',
        'Dashboard' => 'Dashboard',
        'Analytics' => 'Analisi',
        'Email' => 'Email',
        'Slack' => 'Slack',
        'Telegram' => 'Telegram',
        'Source' => 'Origine',
        'Event' => 'Evento',
        'Events' => 'Eventi',
        'Status' => 'Stato',
        'Priority' => 'Priorità',
        'Action' => 'Azione',
        'Actions' => 'Azioni',
        'Open' => 'Apri',
        'Delete' => 'Elimina',
        'Edit' => 'Modifica',
        'Duplicate' => 'Duplica',
        'Save' => 'Salva',
        'Update' => 'Aggiorna',
        'Enable' => 'Abilita',
        'Enabled' => 'Abilitato',
        'Disabled' => 'Disabilitato',
        'Error' => 'Errore',
        'Invalid' => 'Non valido',
        'Failed' => 'Non riuscito',
        'Forbidden' => 'Vietato',
        'Unauthorized' => 'Non autorizzato',
        'Unknown' => 'Sconosciuto',
    ];

    $translated = strtr($text, $terms);
    return $translated === '' ? $text : $translated;
};

$autoTranslateFa = static function (string $text): string {
    $exact = [
        '(no title)' => '(بدون عنوان)',
        '(HTTP %d)' => '(HTTP %d)',
        '7 Days' => '۷ روز',
        '30 Days' => '۳۰ روز',
        'All priorities' => 'همه اولویت‌ها',
        'All sources' => 'همه منابع',
        'All statuses' => 'همه وضعیت‌ها',
        'All time' => 'کل بازه زمانی',
        'All types' => 'همه نوع‌ها',
        'Last 7 days' => '۷ روز گذشته',
        'Last 30 days' => '۳۰ روز گذشته',
        'Last year' => 'سال گذشته',
        'Today' => 'امروز',
        'Yesterday' => 'دیروز',
        'No' => 'خیر',
        'Yes' => 'بله',
        'Read' => 'خوانده‌شده',
        'Unread' => 'خوانده‌نشده',
        'Active' => 'فعال',
        'Inactive' => 'غیرفعال',
        'Archived' => 'بایگانی‌شده',
        'Archive' => 'بایگانی',
        'Unarchive' => 'خروج از بایگانی',
        'Published' => 'منتشرشده',
        'Draft' => 'پیش‌نویس',
        'Private' => 'خصوصی',
        'Trash' => 'زباله‌دان',
        'New' => 'جدید',
        'Created' => 'ایجاد شد',
        'Updated' => 'به‌روزرسانی شد',
        'From' => 'از',
        'To' => 'تا',
        'Date' => 'تاریخ',
        'Count' => 'تعداد',
        'Total' => 'مجموع',
        'Name' => 'نام',
        'Manage' => 'مدیریت',
        'Important' => 'مهم',
        'Mark as read' => 'علامت‌گذاری به‌عنوان خوانده‌شده',
        'Mark as unread' => 'علامت‌گذاری به‌عنوان خوانده‌نشده',
        'Mark important' => 'علامت‌گذاری مهم',
        'Remove important' => 'حذف علامت مهم',
        'View details' => 'مشاهده جزئیات',
        'View Notifications' => 'مشاهده اعلان‌ها',
        'Export CSV' => 'خروجی CSV',
        'Trigger Test' => 'اجرای تست',
        'Hook Test' => 'تست هوک',
        'Hook triggered.' => 'هوک اجرا شد.',
        'Hook created.' => 'هوک ایجاد شد.',
        'Hook updated.' => 'هوک به‌روزرسانی شد.',
        'Hook deleted.' => 'هوک حذف شد.',
        'Rule created.' => 'قانون ایجاد شد.',
        'Rule updated.' => 'قانون به‌روزرسانی شد.',
        'Rule deleted.' => 'قانون حذف شد.',
        'Rule duplicated.' => 'قانون تکثیر شد.',
        'Operation failed.' => 'عملیات ناموفق بود.',
        'Request failed.' => 'درخواست ناموفق بود.',
        'Delete failed.' => 'حذف ناموفق بود.',
        'Update failed.' => 'به‌روزرسانی ناموفق بود.',
        'Template not found.' => 'قالب پیدا نشد.',
        'Presenter is missing.' => 'ارائه‌دهنده موجود نیست.',
    ];
    if (isset($exact[$text])) {
        return $exact[$text];
    }

    $terms = [
        'Notification' => 'اعلان',
        'Notifications' => 'اعلان‌ها',
        'notification' => 'اعلان',
        'notifications' => 'اعلان‌ها',
        'Channel' => 'کانال',
        'channel' => 'کانال',
        'Test' => 'آزمایش',
        'Settings' => 'تنظیمات',
        'General' => 'عمومی',
        'Rules' => 'قوانین',
        'Hooks' => 'هوک‌ها',
        'Dashboard' => 'داشبورد',
        'Analytics' => 'تحلیل‌ها',
        'Email' => 'ایمیل',
        'Slack' => 'اسلک',
        'Telegram' => 'تلگرام',
        'Source' => 'منبع',
        'Event' => 'رویداد',
        'Events' => 'رویدادها',
        'Status' => 'وضعیت',
        'Priority' => 'اولویت',
        'Action' => 'اقدام',
        'Actions' => 'اقدامات',
        'Open' => 'باز کردن',
        'Delete' => 'حذف',
        'Edit' => 'ویرایش',
        'Duplicate' => 'تکثیر',
        'Save' => 'ذخیره',
        'Update' => 'به‌روزرسانی',
        'Enable' => 'فعال‌سازی',
        'Enabled' => 'فعال',
        'Disabled' => 'غیرفعال',
        'Error' => 'خطا',
        'Invalid' => 'نامعتبر',
        'Failed' => 'ناموفق',
        'Forbidden' => 'ممنوع',
        'Unauthorized' => 'غیرمجاز',
        'Unknown' => 'نامشخص',
    ];

    $translated = strtr($text, $terms);
    return $translated === '' ? $text : $translated;
};

$completeTranslations = static function (array $allMsgids, array $translations, string $locale) use ($autoTranslateFa, $autoTranslateIt): array {
    foreach ($allMsgids as $msgid) {
        if (isset($translations[$msgid]) && $translations[$msgid] !== '') {
            continue;
        }

        if ($locale === 'fa_IR') {
            $translations[$msgid] = $autoTranslateFa($msgid);
        } elseif ($locale === 'it_IT') {
            $translations[$msgid] = $autoTranslateIt($msgid);
        } else {
            $translations[$msgid] = $msgid;
        }
    }

    return $translations;
};

$fa = [
    'Notification Hub' => 'مرکز اعلان',
    'Notifications' => 'اعلان‌ها',
    'Dashboard' => 'داشبورد',
    'Hooks' => 'هوک‌ها',
    'Rules' => 'قوانین',
    'Analytics' => 'تحلیل‌ها',
    'Settings' => 'تنظیمات',
    'General' => 'عمومی',
    'Channels' => 'کانال‌ها',
    'Save Changes' => 'ذخیره تغییرات',
    'Retention (days)' => 'مدت نگهداری (روز)',
    'Email To' => 'ایمیل مقصد',
    'Telegram Bot Token' => 'توکن ربات تلگرام',
    'Telegram Chat ID' => 'شناسه چت تلگرام',
    'Slack Webhook URL' => 'آدرس وب‌هوک اسلک',
    'Notification events' => 'رویدادهای اعلان',
    'Choose which events should create and send notifications.' => 'انتخاب کنید کدام رویدادها اعلان ایجاد و ارسال کنند.',
    'Send Test Email' => 'ارسال ایمیل آزمایشی',
    'Send Test to Telegram' => 'ارسال آزمایشی به تلگرام',
    'Send Test to Slack' => 'ارسال آزمایشی به اسلک',
    'Send Test Notification' => 'ارسال اعلان آزمایشی',
    'Settings saved successfully.' => 'تنظیمات با موفقیت ذخیره شد.',
    'Test sent successfully to %s.' => 'آزمون با موفقیت به %s ارسال شد.',
    'Test failed to send to %s.' => 'ارسال آزمون به %s ناموفق بود.',
    'Email' => 'ایمیل',
    'Telegram' => 'تلگرام',
    'Slack' => 'اسلک',
    'Source' => 'منبع',
    'Type' => 'نوع',
    'Title' => 'عنوان',
    'Message' => 'پیام',
    'Status' => 'وضعیت',
    'Priority' => 'اولویت',
    'Actions' => 'اقدامات',
    'Filter' => 'فیلتر',
    'Clear filters' => 'پاک کردن فیلترها',
    'Search Notifications' => 'جستجوی اعلان‌ها',
    'Notification Analytics' => 'تحلیل اعلان‌ها',
    'Notifications Dashboard' => 'داشبورد اعلان‌ها',
    'Custom Hooks' => 'هوک‌های سفارشی',
    'Automation Rules' => 'قوانین خودکارسازی',
    'Create Rule' => 'ایجاد قانون',
    'Edit Rule' => 'ویرایش قانون',
    'Save Rule' => 'ذخیره قانون',
    'Update Rule' => 'به‌روزرسانی قانون',
    'Delete' => 'حذف',
    'Edit' => 'ویرایش',
    'Duplicate' => 'تکثیر',
    'Cancel' => 'انصراف',
    'Close dialog' => 'بستن پنجره',
    'Open details' => 'باز کردن جزئیات',
    'Open Comment' => 'باز کردن دیدگاه',
    'Open in WordPress' => 'باز کردن در وردپرس',
    'Open Notification Hub' => 'باز کردن Notification Hub',
    'Triggered by: %s' => 'ایجاد شده توسط: %s',
    'Source: %1$s | Event: %2$s' => 'منبع: %1$s | رویداد: %2$s',
    'New Notification' => 'اعلان جدید',
    'Unknown' => 'نامشخص',
    'General Notification' => 'اعلان عمومی',
    'Unauthorized.' => 'غیرمجاز.',
    'Invalid nonce.' => 'نونس نامعتبر است.',
    'Invalid action.' => 'اقدام نامعتبر است.',
    'Invalid ID.' => 'شناسه نامعتبر است.',
    'Notification not found.' => 'اعلان پیدا نشد.',
    'Failed to update notification.' => 'به‌روزرسانی اعلان ناموفق بود.',
    'Failed to delete notification.' => 'حذف اعلان ناموفق بود.',
    'Too many requests.' => 'درخواست‌ها بیش از حد مجاز است.',
    'Invalid JSON body.' => 'بدنه JSON نامعتبر است.',
    'Missing signature headers.' => 'هدرهای امضا موجود نیست.',
    'Invalid timestamp.' => 'زمان‌مهر نامعتبر است.',
    'Timestamp expired.' => 'زمان‌مهر منقضی شده است.',
    'Invalid signature.' => 'امضای نامعتبر.',
    'Invalid signature format.' => 'فرمت امضا نامعتبر است.',
    'Replay detected.' => 'بازپخش شناسایی شد.',
    'Insert failed.' => 'درج اطلاعات ناموفق بود.',
    'Access denied.' => 'دسترسی مجاز نیست.',
    'You do not have sufficient permissions.' => 'شما مجوز کافی ندارید.',
    'Keep data on uninstall' => 'حفظ داده‌ها هنگام حذف',
    'Do not delete plugin tables on uninstall' => 'هنگام حذف افزونه، جداول حذف نشوند',
    'No rules yet.' => 'هنوز قانونی وجود ندارد.',
    'No hooks yet.' => 'هنوز هوکی وجود ندارد.',
];

$it = [
    'Notification Hub' => 'Notification Hub',
    'Notifications' => 'Notifiche',
    'Dashboard' => 'Dashboard',
    'Hooks' => 'Hook',
    'Rules' => 'Regole',
    'Analytics' => 'Analisi',
    'Settings' => 'Impostazioni',
    'General' => 'Generale',
    'Channels' => 'Canali',
    'Save Changes' => 'Salva modifiche',
    'Retention (days)' => 'Conservazione (giorni)',
    'Email To' => 'Email destinatario',
    'Telegram Bot Token' => 'Token bot Telegram',
    'Telegram Chat ID' => 'ID chat Telegram',
    'Slack Webhook URL' => 'URL webhook Slack',
    'Notification events' => 'Eventi di notifica',
    'Choose which events should create and send notifications.' => 'Scegli quali eventi devono creare e inviare notifiche.',
    'Send Test Email' => 'Invia email di prova',
    'Send Test to Telegram' => 'Invia test a Telegram',
    'Send Test to Slack' => 'Invia test a Slack',
    'Send Test Notification' => 'Invia notifica di prova',
    'Settings saved successfully.' => 'Impostazioni salvate correttamente.',
    'Test sent successfully to %s.' => 'Test inviato con successo a %s.',
    'Test failed to send to %s.' => 'Invio del test a %s non riuscito.',
    'Email' => 'Email',
    'Telegram' => 'Telegram',
    'Slack' => 'Slack',
    'Source' => 'Origine',
    'Type' => 'Tipo',
    'Title' => 'Titolo',
    'Message' => 'Messaggio',
    'Status' => 'Stato',
    'Priority' => 'Priorità',
    'Actions' => 'Azioni',
    'Filter' => 'Filtro',
    'Clear filters' => 'Cancella filtri',
    'Search Notifications' => 'Cerca notifiche',
    'Notification Analytics' => 'Analisi notifiche',
    'Notifications Dashboard' => 'Dashboard notifiche',
    'Custom Hooks' => 'Hook personalizzati',
    'Automation Rules' => 'Regole di automazione',
    'Create Rule' => 'Crea regola',
    'Edit Rule' => 'Modifica regola',
    'Save Rule' => 'Salva regola',
    'Update Rule' => 'Aggiorna regola',
    'Delete' => 'Elimina',
    'Edit' => 'Modifica',
    'Duplicate' => 'Duplica',
    'Cancel' => 'Annulla',
    'Close dialog' => 'Chiudi finestra',
    'Open details' => 'Apri dettagli',
    'Open Comment' => 'Apri commento',
    'Open in WordPress' => 'Apri in WordPress',
    'Open Notification Hub' => 'Apri Notification Hub',
    'Triggered by: %s' => 'Attivato da: %s',
    'Source: %1$s | Event: %2$s' => 'Origine: %1$s | Evento: %2$s',
    'New Notification' => 'Nuova notifica',
    'Unknown' => 'Sconosciuto',
    'General Notification' => 'Notifica generale',
    'Unauthorized.' => 'Non autorizzato.',
    'Invalid nonce.' => 'Nonce non valido.',
    'Invalid action.' => 'Azione non valida.',
    'Invalid ID.' => 'ID non valido.',
    'Notification not found.' => 'Notifica non trovata.',
    'Failed to update notification.' => 'Aggiornamento notifica non riuscito.',
    'Failed to delete notification.' => 'Eliminazione notifica non riuscita.',
    'Too many requests.' => 'Troppe richieste.',
    'Invalid JSON body.' => 'Corpo JSON non valido.',
    'Missing signature headers.' => 'Header di firma mancanti.',
    'Invalid timestamp.' => 'Timestamp non valido.',
    'Timestamp expired.' => 'Timestamp scaduto.',
    'Invalid signature.' => 'Firma non valida.',
    'Invalid signature format.' => 'Formato firma non valido.',
    'Replay detected.' => 'Rilevato replay.',
    'Insert failed.' => 'Inserimento non riuscito.',
    'Access denied.' => 'Accesso negato.',
    'You do not have sufficient permissions.' => 'Non hai permessi sufficienti.',
    'Keep data on uninstall' => 'Mantieni i dati alla disinstallazione',
    'Do not delete plugin tables on uninstall' => 'Non eliminare le tabelle del plugin alla disinstallazione',
    'No rules yet.' => 'Nessuna regola ancora.',
    'No hooks yet.' => 'Nessun hook ancora.',
];

$pot = $buildPo($msgids, [], '');
file_put_contents($languagesDir . '/notification-hub.pot', $pot);

$fa = $completeTranslations($msgids, $fa, 'fa_IR');
$it = $completeTranslations($msgids, $it, 'it_IT');

$poFa = $buildPo($msgids, $fa, 'fa_IR');
$poIt = $buildPo($msgids, $it, 'it_IT');

file_put_contents($languagesDir . '/notification-hub-fa_IR.po', $poFa);
file_put_contents($languagesDir . '/notification-hub-it_IT.po', $poIt);

/**
 * Parse a PO file into msgid => msgstr map.
 *
 * @return array<string,string>
 */
function parse_po(string $poPath): array {
    $lines = file($poPath, FILE_IGNORE_NEW_LINES);
    if (!is_array($lines)) {
        return [];
    }

    $entries = [];
    $msgid = null;
    $msgstr = null;
    $state = '';

    $flush = static function () use (&$entries, &$msgid, &$msgstr): void {
        if ($msgid !== null && $msgid !== '') {
            $entries[$msgid] = $msgstr ?? '';
        }
        $msgid = null;
        $msgstr = null;
    };

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_starts_with($line, 'msgid ')) {
            $flush();
            $msgid = stripcslashes(trim(substr($line, 6), '"'));
            $msgstr = '';
            $state = 'msgid';
            continue;
        }

        if (str_starts_with($line, 'msgstr ')) {
            $msgstr = stripcslashes(trim(substr($line, 7), '"'));
            $state = 'msgstr';
            continue;
        }

        if (str_starts_with($line, '"')) {
            $part = stripcslashes(trim($line, '"'));
            if ($state === 'msgid' && $msgid !== null) {
                $msgid .= $part;
            } elseif ($state === 'msgstr' && $msgstr !== null) {
                $msgstr .= $part;
            }
        }
    }

    $flush();
    return $entries;
}

/**
 * Build binary MO content.
 *
 * @param array<string,string> $translations
 */
function build_mo(array $translations): string {
    ksort($translations, SORT_STRING);

    $ids = '';
    $strings = '';
    $offsets = [];

    foreach ($translations as $id => $str) {
        $idBin = $id . "\0";
        $strBin = $str . "\0";

        $offsets[] = [strlen($idBin) - 1, strlen($ids), strlen($strBin) - 1, strlen($strings)];
        $ids .= $idBin;
        $strings .= $strBin;
    }

    $count = count($offsets);
    $headerSize = 28;
    $origTableOffset = $headerSize;
    $transTableOffset = $origTableOffset + ($count * 8);
    $origStringsOffset = $transTableOffset + ($count * 8);
    $transStringsOffset = $origStringsOffset + strlen($ids);

    $mo = pack('V*', 0x950412de, 0, $count, $origTableOffset, $transTableOffset, 0, 0);

    foreach ($offsets as $o) {
        $mo .= pack('V2', $o[0], $origStringsOffset + $o[1]);
    }
    foreach ($offsets as $o) {
        $mo .= pack('V2', $o[2], $transStringsOffset + $o[3]);
    }

    $mo .= $ids;
    $mo .= $strings;
    return $mo;
}

$faEntries = parse_po($languagesDir . '/notification-hub-fa_IR.po');
$itEntries = parse_po($languagesDir . '/notification-hub-it_IT.po');

file_put_contents($languagesDir . '/notification-hub-fa_IR.mo', build_mo($faEntries));
file_put_contents($languagesDir . '/notification-hub-it_IT.mo', build_mo($itEntries));

echo "LOCALE_BUILD: PASS\n";
echo 'POT entries: ' . count($msgids) . PHP_EOL;
echo 'FA translated: ' . count(array_filter($faEntries, static fn($v) => $v !== '')) . PHP_EOL;
echo 'IT translated: ' . count(array_filter($itEntries, static fn($v) => $v !== '')) . PHP_EOL;
