<?php
/*
Plugin Name: ConveyThis Translate
Plugin URI: https://www.conveythis.com/?utm_source=widget&utm_medium=wordpress
Description: Translate your WordPress site into over 100 languages using professional and instant machine translation technology. ConveyThis will help provide you with an SEO-friendy, multilingual website in minutes with no coding required.
Version: 164
Author: ConveyThis Translate Team
Author URI: https://www.conveythis.com/?utm_source=widget&utm_medium=wordpress
Text Domain: conveythis-translate
License: GPL2
*/

//for debuging:
//file_put_contents(ABSPATH.'/tmp/test_txt.txt', "some text\n", FILE_APPEND);

// ini_set( 'error_reporting', E_ALL );
// ini_set( 'display_errors', 1 );
// ini_set( 'display_startup_errors', 1 );

if( !defined( 'ABSPATH' ) )
{
    exit;
}

//defines.
define('CONVEY_PLUGIN_PATH', plugin_dir_url(__FILE__));
define('CONVEY_PLUGIN_DIR', plugins_url('', __FILE__));
define('CONVEYTHIS_URL', 'https://www.conveythis.com/');
define('CONVEYTHIS_APP_URL', 'https://app.conveythis.com');
define('CONVEYTHIS_API_URL', 'https://api.conveythis.com');
define('CONVEYTHIS_API_PROXY_URL', 'https://api-proxy.conveythis.com');
define('CONVEYTHIS_JAVASCRIPT_PLUGIN_URL', '//cdn.conveythis.com/javascript/65');
define('CONVEYTHIS_JAVASCRIPT_LIGHT_PLUGIN_URL', '//cdn.conveythis.com/javascriptLight/3');

if (!defined('CONVEYTHIS_CACHE_ROOT_PATH')) {
    define('CONVEYTHIS_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/');
}
define('CONVEYTHIS_CACHE_PATH', CONVEYTHIS_CACHE_ROOT_PATH . 'conveythis/');
define('CONVEYTHIS_CACHE_SLUG_PATH', CONVEYTHIS_CACHE_PATH . 'slug.json');
define('CONVEYTHIS_CACHE_TRANSLATIONS_PATH', CONVEYTHIS_CACHE_PATH . 'translations/');
define('API_AUTH_TOKEN', '85T8DGNtV88g4wvceVyHym69Yu3v5ZmN');

if (isset($_POST['set_api_key']) && $_POST['set_api_key'] == 1) {
    update_option('api_key', $_POST['api_key']);
}

if (isset($_POST['convey_event']) && strlen($_POST['convey_event_name'] > 0)) {
    $convey = ConveyThis::Instance();
    $convey::sendEvent($_POST['convey_event_name']);
    die(json_encode(true));
}

class ConveyThis
{
    static $instance;

    var $segments = [];
    var $segments_hash = [];
    var $items = [];
    var $links = [];
    var $exclusions = [];
    var $glossary = [];
    var $exclusion_blocks = [];
    var $exclusion_block_ids = [];
    var $account;
    var $api_key = '';
    var $source_language = '';
    var $target_languages = '';
    var $default_language = '';
    var $target_languages_translations = '';
    var $language_code = '';
    var $site_url;
    var $site_host;
    var $site_prefix;
    var $plan = 'free';
    private $translate_media;
    private $translate_document;
    private $translate_links;
    private $show_widget = true;
    private $exceeded = false;

    var $shortcode_counter = 0;

    public $pluginPath;

    public $cacheTranslateSize = 0;

    var $imageExt = [
        'gif',
        'jpg',
        'jpeg',
        'png',
        'webp',
        'svg'
    ];

    var $documentExt = [
        'pdf'
    ];

    var $avoidUrlExt = [
        'pdf',
        'xml',
        'xlsx',
        'docx',
        'gif',
        'jpg',
        'jpeg',
        'png',
        'mp3',
        'mp4',
    ];

    // Luxemb - Luxembourgish
    // Haitian - Haitian (Creole)

    var $siblingsAllowArray = ["A", "ABBR", "ACRONYM", "BDO", "BDI", "STRONG","BR","SPAN", "EM", "I", "B", "CITE", "DEL", "DFN", "INS", "MARK", "Q", "BIG", "SMALL", "SUB", "SUP", "U"];
    var $siblingsAvoidArray = ["P", "DIV", "H1", "H2", "H3", "H4", "H5", "H6", "LABEL", "LI", "SVG", "PRE"];

    var $languages = array(
        703 => array('language_id' => 703, 'title_en' => 'English', 'title' => 'English', 'code2' => 'en', 'code3' => 'eng', 'flag' => 'R04'),
        704 => array('language_id' => 704, 'title_en' => 'Afrikaans', 'title' => 'Afrikaans', 'code2' => 'af', 'code3' => 'afr', 'flag' => '7xS'),
        705 => array('language_id' => 705, 'title_en' => 'Albanian', 'title' => 'Shqip', 'code2' => 'sq', 'code3' => 'sqi', 'flag' => '5iM'),
        706 => array('language_id' => 706, 'title_en' => 'Amharic', 'title' => 'አማርኛ', 'code2' => 'am', 'code3' => 'amh', 'flag' => 'ZH1'),
        707 => array('language_id' => 707, 'title_en' => 'Arabic', 'title' => 'العربية', 'code2' => 'ar', 'code3' => 'ara', 'flag' => 'J06', 'rtl' => 1),
        708 => array('language_id' => 708, 'title_en' => 'Armenian', 'title' => 'Հայերեն', 'code2' => 'hy', 'code3' => 'hye', 'flag' => 'q9U'),
        709 => array('language_id' => 709, 'title_en' => 'Azerbaijan', 'title' => 'Azərbaycanca', 'code2' => 'az', 'code3' => 'aze', 'flag' => 'Wg1'),
        710 => array('language_id' => 710, 'title_en' => 'Bashkir', 'title' => 'Башҡортса', 'code2' => 'ba', 'code3' => 'bak', 'flag' => 'D1H'),
        711 => array('language_id' => 711, 'title_en' => 'Basque', 'title' => 'Euskara', 'code2' => 'eu', 'code3' => 'eus', 'flag' => ''),
        712 => array('language_id' => 712, 'title_en' => 'Belarusian', 'title' => 'Беларуская', 'code2' => 'be', 'code3' => 'bel', 'flag' => 'O8S'),
        713 => array('language_id' => 713, 'title_en' => 'Bengali', 'title' => 'বাংলা', 'code2' => 'bn', 'code3' => 'ben', 'flag' => '63A'),
        714 => array('language_id' => 714, 'title_en' => 'Bosnian', 'title' => 'Bosanski', 'code2' => 'bs', 'code3' => 'bos', 'flag' => 'Z1t'),
        715 => array('language_id' => 715, 'title_en' => 'Bulgarian', 'title' => 'Български', 'code2' => 'bg', 'code3' => 'bul', 'flag' => 'V3p'),
        716 => array('language_id' => 716, 'title_en' => 'Burmese', 'title' => 'မြန်မာဘာသာ', 'code2' => 'my', 'code3' => 'mya', 'flag' => 'YB9'),
        717 => array('language_id' => 717, 'title_en' => 'Catalan', 'title' => 'Català', 'code2' => 'ca', 'code3' => 'cat', 'flag' => 'Pw6'),
        718 => array('language_id' => 718, 'title_en' => 'Cebuano', 'title' => 'Cebuano', 'code2' => 'ceb', 'code3' => 'ceb', 'flag' => ''),
        719 => array('language_id' => 719, 'title_en' => 'Chinese (Simplified)', 'title' => '简体', 'code2' => 'zh', 'code3' => 'zho-sim', 'flag' => 'Z1v'),
        720 => array('language_id' => 720, 'title_en' => 'Croatian', 'title' => 'Hrvatski', 'code2' => 'hr', 'code3' => 'hrv', 'flag' => '7KQ'),
        721 => array('language_id' => 721, 'title_en' => 'Czech', 'title' => 'Čeština', 'code2' => 'cs', 'code3' => 'cze', 'flag' => '1ZY'),
        722 => array('language_id' => 722, 'title_en' => 'Danish', 'title' => 'Dansk', 'code2' => 'da', 'code3' => 'dan', 'flag' => 'Ro2'),
        723 => array('language_id' => 723, 'title_en' => 'Dutch', 'title' => 'Nederlands', 'code2' => 'nl', 'code3' => 'nld', 'flag' => '8jV'),
        724 => array('language_id' => 724, 'title_en' => 'Esperanto', 'title' => 'Esperanto', 'code2' => 'eo', 'code3' => 'epo', 'flag' => 'Dw0'),
        725 => array('language_id' => 725, 'title_en' => 'Estonian', 'title' => 'Eesti', 'code2' => 'et', 'code3' => 'est', 'flag' => 'VJ8'),
        726 => array('language_id' => 726, 'title_en' => 'Finnish', 'title' => 'Suomi', 'title' => 'Finnish', 'code2' => 'fi', 'code3' => 'fin', 'flag' => 'nM4'),
        727 => array('language_id' => 727, 'title_en' => 'French', 'title' => 'Français', 'code2' => 'fr', 'code3' => 'fre', 'flag' => 'E77'),
        728 => array('language_id' => 728, 'title_en' => 'Galician', 'title' => 'Galego', 'code2' => 'gl', 'code3' => 'glg', 'flag' => 'A5d'),
        729 => array('language_id' => 729, 'title_en' => 'Georgian', 'title' => 'ქართული', 'code2' => 'ka', 'code3' => 'kat', 'flag' => '8Ou'),
        730 => array('language_id' => 730, 'title_en' => 'German', 'title' => 'Deutsch', 'code2' => 'de', 'code3' => 'ger', 'flag' => 'K7e'),
        731 => array('language_id' => 731, 'title_en' => 'Greek', 'title' => 'Ελληνικά', 'code2' => 'el', 'code3' => 'ell', 'flag' => 'kY8'),
        732 => array('language_id' => 732, 'title_en' => 'Gujarati', 'title' => 'ગુજરાતી', 'code2' => 'gu', 'code3' => 'guj', 'flag' => 'My6'),
        733 => array('language_id' => 733, 'title_en' => 'Haitian', 'title' => 'Kreyòl Ayisyen', 'code2' => 'ht', 'code3' => 'hat', 'flag' => ''),
        734 => array('language_id' => 734, 'title_en' => 'Hebrew', 'title' => 'עברית', 'code2' => 'he', 'code3' => 'heb', 'flag' => '5KS', 'rtl' => 1),
        735 => array('language_id' => 735, 'title_en' => 'Hill Mari', 'title' => 'Курыкмарий', 'code2' => 'mrj', 'code3' => 'mrj', 'flag' => ''),
        736 => array('language_id' => 736, 'title_en' => 'Hindi', 'title' => 'हिन्दी', 'code2' => 'hi', 'code3' => 'hin', 'flag' => 'My6'),
        737 => array('language_id' => 737, 'title_en' => 'Hungarian', 'title' => 'Magyar', 'code2' => 'hu', 'code3' => 'hun', 'flag' => 'OU2'),
        738 => array('language_id' => 738, 'title_en' => 'Icelandic', 'title' => 'Íslenska', 'code2' => 'is', 'code3' => 'isl', 'flag' => 'Ho8'),
        739 => array('language_id' => 739, 'title_en' => 'Indonesian', 'title' => 'Bahasa Indonesia', 'code2' => 'id', 'code3' => 'ind', 'flag' => 't0X'),
        740 => array('language_id' => 740, 'title_en' => 'Irish', 'title' => 'Gaeilge', 'code2' => 'ga', 'code3' => 'gle', 'flag' => '5Tr'),
        741 => array('language_id' => 741, 'title_en' => 'Italian', 'title' => 'Italiano', 'code2' => 'it', 'code3' => 'ita', 'flag' => 'BW7'),
        742 => array('language_id' => 742, 'title_en' => 'Japanese', 'title' => '日本語', 'code2' => 'ja', 'code3' => 'jpn', 'flag' => '4YX'),
        743 => array('language_id' => 743, 'title_en' => 'Javanese', 'title' => 'Basa Jawa', 'code2' => 'jv', 'code3' => 'jav', 'flag' => 'C9k'),
        744 => array('language_id' => 744, 'title_en' => 'Kannada', 'title' => 'ಕನ್ನಡ', 'code2' => 'kn', 'code3' => 'kan', 'flag' => 'My6'),
        745 => array('language_id' => 745, 'title_en' => 'Kazakh', 'title' => 'Қазақша', 'code2' => 'kk', 'code3' => 'kaz', 'flag' => 'QA5'),
        746 => array('language_id' => 746, 'title_en' => 'Khmer', 'title' => 'ភាសាខ្មែរ', 'code2' => 'km', 'code3' => 'khm', 'flag' => 'o8B'),
        747 => array('language_id' => 747, 'title_en' => 'Korean', 'title' => '한국어', 'code2' => 'ko', 'code3' => 'kor', 'flag' => '0W3'),
        748 => array('language_id' => 748, 'title_en' => 'Kyrgyz', 'title' => 'Кыргызча', 'code2' => 'ky', 'code3' => 'kir', 'flag' => 'uP6'),
        749 => array('language_id' => 749, 'title_en' => 'Laotian', 'title' => 'ພາສາລາວ', 'code2' => 'lo', 'code3' => 'lao', 'flag' => 'Qy5'),
        750 => array('language_id' => 750, 'title_en' => 'Latin', 'title' => 'Latina', 'code2' => 'la', 'code3' => 'lat', 'flag' => 'BW7'),
        751 => array('language_id' => 751, 'title_en' => 'Latvian', 'title' => 'Latviešu', 'code2' => 'lv', 'code3' => 'lav', 'flag' => 'j1D'),
        752 => array('language_id' => 752, 'title_en' => 'Lithuanian', 'title' => 'Lietuvių', 'code2' => 'lt', 'code3' => 'lit', 'flag' => 'uI6'),
        753 => array('language_id' => 753, 'title_en' => 'Luxemb', 'title' => 'Lëtzebuergesch', 'code2' => 'lb', 'code3' => 'ltz', 'flag' => 'EV8'),
        754 => array('language_id' => 754, 'title_en' => 'Macedonian', 'title' => 'Македонски', 'code2' => 'mk', 'code3' => 'mkd', 'flag' => '6GV'),
        755 => array('language_id' => 755, 'title_en' => 'Malagasy', 'title' => 'Malagasy', 'code2' => 'mg', 'code3' => 'mlg', 'flag' => '4tE'),
        756 => array('language_id' => 756, 'title_en' => 'Malay', 'title' => 'Bahasa Melayu', 'code2' => 'ms', 'code3' => 'msa', 'flag' => 'C9k'),
        757 => array('language_id' => 757, 'title_en' => 'Malayalam', 'title' => 'മലയാളം', 'code2' => 'ml', 'code3' => 'mal', 'flag' => 'My6'),
        758 => array('language_id' => 758, 'title_en' => 'Maltese', 'title' => 'Malti', 'code2' => 'mt', 'code3' => 'mlt', 'flag' => 'N11'),
        759 => array('language_id' => 759, 'title_en' => 'Maori', 'title' => 'Māori', 'code2' => 'mi', 'code3' => 'mri', 'flag' => '0Mi'),
        760 => array('language_id' => 760, 'title_en' => 'Marathi', 'title' => 'मराठी', 'code2' => 'mr', 'code3' => 'mar', 'flag' => 'My6'),
        761 => array('language_id' => 761, 'title_en' => 'Mari', 'title' => 'Мари́йский', 'code2' => 'mhr', 'code3' => 'chm', 'flag' => ''),
        762 => array('language_id' => 762, 'title_en' => 'Mongolian', 'title' => 'Монгол', 'code2' => 'mn', 'code3' => 'mon', 'flag' => 'X8h'),
        763 => array('language_id' => 763, 'title_en' => 'Nepali', 'title' => 'नेपाली', 'code2' => 'ne', 'code3' => 'nep', 'flag' => 'E0c'),
        764 => array('language_id' => 764, 'title_en' => 'Norwegian', 'title' => 'Norsk', 'code2' => 'no', 'code3' => 'nor', 'flag' => '4KE'),
        765 => array('language_id' => 765, 'title_en' => 'Papiamento', 'title' => 'E Papiamento', 'code2' => 'pap', 'code3' => 'pap', 'flag' => ''),
        766 => array('language_id' => 766, 'title_en' => 'Persian', 'title' => 'فارسی', 'code2' => 'fa', 'code3' => 'per', 'flag' => 'Vo7', 'rtl' => 1),
        767 => array('language_id' => 767, 'title_en' => 'Polish', 'title' => 'Polski', 'code2' => 'pl', 'code3' => 'pol', 'flag' => 'j0R'),
        768 => array('language_id' => 768, 'title_en' => 'Portuguese', 'title' => 'Português', 'code2' => 'pt', 'code3' => 'por', 'flag' => '1oU'),
        769 => array('language_id' => 769, 'title_en' => 'Punjabi', 'title' => 'ਪੰਜਾਬੀ', 'code2' => 'pa', 'code3' => 'pan', 'flag' => 'n4T'),
        770 => array('language_id' => 770, 'title_en' => 'Romanian', 'title' => 'Română', 'code2' => 'ro', 'code3' => 'rum', 'flag' => 'V5u'),
        771 => array('language_id' => 771, 'title_en' => 'Russian', 'title' => 'Русский', 'code2' => 'ru', 'code3' => 'rus', 'flag' => 'D1H'),
        772 => array('language_id' => 772, 'title_en' => 'Scottish', 'title' => 'Gàidhlig', 'code2' => 'gd', 'code3' => 'gla', 'flag' => '9MI'),
        773 => array('language_id' => 773, 'title_en' => 'Serbian', 'title' => 'Српски', 'code2' => 'sr', 'code3' => 'srp', 'flag' => 'GC6'),
        774 => array('language_id' => 774, 'title_en' => 'Sinhala', 'title' => 'සිංහල', 'code2' => 'si', 'code3' => 'sin', 'flag' => '9JL'),
        775 => array('language_id' => 775, 'title_en' => 'Slovakian', 'title' => 'Slovenčina', 'code2' => 'sk', 'code3' => 'slk', 'flag' => 'Y2i'),
        776 => array('language_id' => 776, 'title_en' => 'Slovenian', 'title' => 'Slovenščina', 'code2' => 'sl', 'code3' => 'slv', 'flag' => 'ZR1'),
        777 => array('language_id' => 777, 'title_en' => 'Spanish', 'title' => 'Español', 'code2' => 'es', 'code3' => 'spa', 'flag' => 'A5d'),
        778 => array('language_id' => 778, 'title_en' => 'Sundanese', 'title' => 'Basa Sunda', 'code2' => 'su', 'code3' => 'sun', 'flag' => 'Wh1'),
        779 => array('language_id' => 779, 'title_en' => 'Swahili', 'title' => 'Kiswahili', 'code2' => 'sw', 'code3' => 'swa', 'flag' => 'X3y'),
        780 => array('language_id' => 780, 'title_en' => 'Swedish', 'title' => 'Svenska', 'code2' => 'sv', 'code3' => 'swe', 'flag' => 'oZ3'),
        781 => array('language_id' => 781, 'title_en' => 'Tagalog', 'title' => 'Tagalog', 'code2' => 'tl', 'code3' => 'tgl', 'flag' => '2qL'),
        782 => array('language_id' => 782, 'title_en' => 'Tajik', 'title' => 'Тоҷикӣ', 'code2' => 'tg', 'code3' => 'tgk', 'flag' => '7Qa'),
        783 => array('language_id' => 783, 'title_en' => 'Tamil', 'title' => 'தமிழ்', 'code2' => 'ta', 'code3' => 'tam', 'flag' => 'My6'),
        784 => array('language_id' => 784, 'title_en' => 'Tatar', 'title' => 'Татарча', 'code2' => 'tt', 'code3' => 'tat', 'flag' => 'D1H'),
        785 => array('language_id' => 785, 'title_en' => 'Telugu', 'title' => 'తెలుగు', 'code2' => 'te', 'code3' => 'tel', 'flag' => 'My6'),
        786 => array('language_id' => 786, 'title_en' => 'Thai', 'title' => 'ภาษาไทย', 'code2' => 'th', 'code3' => 'tha', 'flag' => 'V6r'),
        787 => array('language_id' => 787, 'title_en' => 'Turkish', 'title' => 'Türkçe', 'code2' => 'tr', 'code3' => 'tur', 'flag' => 'YZ9'),
        788 => array('language_id' => 788, 'title_en' => 'Udmurt', 'title' => 'Удму́рт дунне́', 'code2' => 'udm', 'code3' => 'udm', 'flag' => ''),
        789 => array('language_id' => 789, 'title_en' => 'Ukrainian', 'title' => 'Українська', 'code2' => 'uk', 'code3' => 'ukr', 'flag' => '2Mg'),
        790 => array('language_id' => 790, 'title_en' => 'Urdu', 'title' => 'اردو', 'code2' => 'ur', 'code3' => 'urd', 'flag' => 'n4T', 'rtl' => 1),
        791 => array('language_id' => 791, 'title_en' => 'Uzbek', 'title' => 'O‘zbek', 'code2' => 'uz', 'code3' => 'uzb', 'flag' => 'zJ3'),
        792 => array('language_id' => 792, 'title_en' => 'Vietnamese', 'title' => 'Tiếng Việt', 'code2' => 'vi', 'code3' => 'vie', 'flag' => 'l2A'),
        793 => array('language_id' => 793, 'title_en' => 'Welsh', 'title' => 'Cymraeg', 'code2' => 'cy', 'code3' => 'wel', 'flag' => 'D4b'),
        794 => array('language_id' => 794, 'title_en' => 'Xhosa', 'title' => 'isiXhosa', 'code2' => 'xh', 'code3' => 'xho', 'flag' => '7xS'),
        795 => array('language_id' => 795, 'title_en' => 'Yiddish', 'title' => 'ייִדיש', 'code2' => 'yi', 'code3' => 'yid', 'flag' => '5KS'),
        796 => array('language_id' => 796, 'title_en' => 'Chinese (Traditional)', 'title' => '繁體', 'code2' => 'zh-tw', 'code3' => 'zho-tra', 'flag' => 'Z1v'),
        797 => array('language_id' => 797, 'title_en' => 'Somali', 'title' => 'Soomaali', 'code2' => 'so', 'code3' => 'som', 'flag' => '3fH'),
        798 => array('language_id' => 798, 'title_en' => 'Corsican', 'title' => 'Corsu', 'code2' => 'co', 'code3' => 'cos', 'flag' => 'E77'),
        799 => array('language_id' => 799, 'title_en' => 'Frisian', 'title' => 'Frysk', 'code2' => 'fy', 'code3' => 'fry', 'flag' => '8jV'),
        800 => array('language_id' => 800, 'title_en' => 'Hausa', 'title' => 'Hausa', 'code2' => 'ha', 'code3' => 'hau', 'flag' => '8oM'),
        801 => array('language_id' => 801, 'title_en' => 'Hawaiian', 'title' => 'Ōlelo Hawaiʻi', 'code2' => 'haw', 'code3' => 'haw', 'flag' => '00P'),
        802 => array('language_id' => 802, 'title_en' => 'Hmong', 'title' => 'Hmong', 'code2' => 'hmn', 'code3' => 'hmn', 'flag' => 'Z1v'),
        803 => array('language_id' => 803, 'title_en' => 'Igbo', 'title' => 'Igbo', 'code2' => 'ig', 'code3' => 'ibo', 'flag' => '8oM'),
        804 => array('language_id' => 804, 'title_en' => 'Kinyarwanda', 'title' => 'Kinyarwanda', 'code2' => 'rw', 'code3' => 'kin', 'flag' => '8UD'),
        805 => array('language_id' => 805, 'title_en' => 'Kurdish', 'title' => 'Kurdî', 'code2' => 'ku', 'code3' => 'kur', 'flag' => 'YZ9'),
        806 => array('language_id' => 806, 'title_en' => 'Chichewa', 'title' => 'Chichewa', 'code2' => 'ny', 'code3' => 'nya', 'flag' => 'O9C'),
        807 => array('language_id' => 807, 'title_en' => 'Odia', 'title' => 'ଓଡିଆ', 'code2' => 'or', 'code3' => 'ori', 'flag' => 'My6'),
        808 => array('language_id' => 808, 'title_en' => 'Samoan', 'title' => 'Faasamoa', 'code2' => 'sm', 'code3' => 'smo', 'flag' => '54E'),
        809 => array('language_id' => 809, 'title_en' => 'Sesotho', 'title' => 'Sesotho', 'code2' => 'st', 'code3' => 'sot', 'flag' => '7xS'),
        810 => array('language_id' => 810, 'title_en' => 'Shona', 'title' => 'Shona', 'code2' => 'sn', 'code3' => 'sna', 'flag' => '80Y'),
        811 => array('language_id' => 811, 'title_en' => 'Sindhi', 'title' => 'سنڌي', 'code2' => 'sd', 'code3' => 'snd', 'flag' => 'n4T'),
        812 => array('language_id' => 812, 'title_en' => 'Turkmen', 'title' => 'Türkmenler', 'code2' => 'tk', 'code3' => 'tuk', 'flag' => 'Tm5'),
        813 => array('language_id' => 813, 'title_en' => 'Uyghur', 'title' => 'ئۇيغۇر', 'code2' => 'ug', 'code3' => 'uig', 'flag' => 'Z1v'),
        814 => array('language_id' => 814, 'title_en' => 'Yoruba', 'title' => 'Yoruba', 'code2' => 'yo', 'code3' => 'yor', 'flag' => '8oM'),
        815 => array('language_id' => 815, 'title_en' => 'Zulu', 'title' => 'Zulu', 'code2' => 'zu', 'code3' => 'zul', 'flag' => '7xS'),
    );

    var $flags = array(
        312 => array( 'flag_id' => 312, 'title' => 'Afghanistan', 'code' => 'NV2'),
        313 => array( 'flag_id' => 313, 'title' => 'Albania', 'code' => '5iM'),
        314 => array( 'flag_id' => 314, 'title' => 'Algeria', 'code' => '5W5'),
        315 => array( 'flag_id' => 315, 'title' => 'Andorra', 'code' => '0Iu'),
        316 => array( 'flag_id' => 316, 'title' => 'Angola', 'code' => 'R3d'),
        317 => array( 'flag_id' => 317, 'title' => 'Antigua and Barbuda', 'code' => '16M'),
        318 => array( 'flag_id' => 318, 'title' => 'Argentina', 'code' => 'V1f'),
        319 => array( 'flag_id' => 319, 'title' => 'Armenia', 'code' => 'q9U'),
        320 => array( 'flag_id' => 320, 'title' => 'Australia', 'code' => '2Os'),
        321 => array( 'flag_id' => 321, 'title' => 'Austria', 'code' => '8Dv'),
        322 => array( 'flag_id' => 322, 'title' => 'Azerbaijan', 'code' => 'Wg1'),
        323 => array( 'flag_id' => 323, 'title' => 'Bahamas', 'code' => '0qL'),
        324 => array( 'flag_id' => 324, 'title' => 'Bahrain', 'code' => 'D9A'),
        325 => array( 'flag_id' => 325, 'title' => 'Bangladesh', 'code' => '63A'),
        326 => array( 'flag_id' => 326, 'title' => 'Barbados', 'code' => 'u7L'),
        327 => array( 'flag_id' => 327, 'title' => 'Belarus', 'code' => 'O8S'),
        328 => array( 'flag_id' => 328, 'title' => 'Belgium', 'code' => '0AT'),
        329 => array( 'flag_id' => 329, 'title' => 'Belize', 'code' => 'lH4'),
        330 => array( 'flag_id' => 330, 'title' => 'Benin', 'code' => 'I2x'),
        331 => array( 'flag_id' => 331, 'title' => 'Bhutan', 'code' => 'D9z'),
        332 => array( 'flag_id' => 332, 'title' => 'Bolgariya', 'code' => 'V3p'),
        333 => array( 'flag_id' => 333, 'title' => 'Bolivia', 'code' => '8Vs'),
        334 => array( 'flag_id' => 334, 'title' => 'Bosnia and Herzegovina', 'code' => 'Z1t'),
        335 => array( 'flag_id' => 335, 'title' => 'Botswana', 'code' => 'Vf3'),
        336 => array( 'flag_id' => 336, 'title' => 'Brazil', 'code' => '1oU'),
        337 => array( 'flag_id' => 337, 'title' => 'Brunei', 'code' => '3rE'),
        338 => array( 'flag_id' => 338, 'title' => 'Burkina Faso', 'code' => 'x8P'),
        339 => array( 'flag_id' => 339, 'title' => 'Burundi', 'code' => '5qZ'),
        340 => array( 'flag_id' => 340, 'title' => 'Cambodia', 'code' => 'o8B'),
        341 => array( 'flag_id' => 341, 'title' => 'Cameroon', 'code' => '3cO'),
        342 => array( 'flag_id' => 342, 'title' => 'Canada', 'code' => 'P4g'),
        343 => array( 'flag_id' => 343, 'title' => 'Cape Verde', 'code' => 'R5O'),
        344 => array( 'flag_id' => 344, 'title' => 'Central African Republic', 'code' => 'kN9'),
        345 => array( 'flag_id' => 345, 'title' => 'Chad', 'code' => 'V5u'),
        346 => array( 'flag_id' => 346, 'title' => 'Chile', 'code' => 'wY3'),
        347 => array( 'flag_id' => 347, 'title' => 'China', 'code' => 'Z1v'),
        348 => array( 'flag_id' => 348, 'title' => 'Colombia', 'code' => 'a4S'),
        349 => array( 'flag_id' => 349, 'title' => 'Comoros', 'code' => 'N6k'),
        350 => array( 'flag_id' => 350, 'title' => 'Congo', 'code' => 'WK0'),
        351 => array( 'flag_id' => 351, 'title' => 'Costa Rica', 'code' => 'PP7'),
        352 => array( 'flag_id' => 352, 'title' => 'Cote d\'Ivoire', 'code' => '6PX'),
        353 => array( 'flag_id' => 353, 'title' => 'Croatia', 'code' => '7KQ'),
        354 => array( 'flag_id' => 354, 'title' => 'Cuba', 'code' => 'vU2'),
        355 => array( 'flag_id' => 355, 'title' => 'Cyprys', 'code' => 'Gw4'),
        356 => array( 'flag_id' => 356, 'title' => 'Czech Republic', 'code' => '1ZY'),
        357 => array( 'flag_id' => 357, 'title' => 'Democratic Republic of the Congo', 'code' => 'Kv5'),
        358 => array( 'flag_id' => 358, 'title' => 'Denmark', 'code' => 'Ro2'),
        359 => array( 'flag_id' => 359, 'title' => 'Djibouti', 'code' => 'MS7'),
        360 => array( 'flag_id' => 360, 'title' => 'Dominica', 'code' => 'E7U'),
        361 => array( 'flag_id' => 361, 'title' => 'Dominican Republic', 'code' => 'Eu2'),
        362 => array( 'flag_id' => 362, 'title' => 'Ecuador', 'code' => 'D90'),
        363 => array( 'flag_id' => 363, 'title' => 'Egypt', 'code' => '7LL'),
        364 => array( 'flag_id' => 364, 'title' => 'El Salvador', 'code' => '0zL'),
        365 => array( 'flag_id' => 365, 'title' => 'Equatorial Guinea', 'code' => 'b8T'),
        366 => array( 'flag_id' => 366, 'title' => 'Eritrea', 'code' => '8Gl'),
        367 => array( 'flag_id' => 367, 'title' => 'Estonia', 'code' => 'VJ8'),
        368 => array( 'flag_id' => 368, 'title' => 'Ethiopia', 'code' => 'ZH1'),
        369 => array( 'flag_id' => 369, 'title' => 'Fiji', 'code' => 'E1f'),
        370 => array( 'flag_id' => 370, 'title' => 'Finland', 'code' => 'nM4'),
        371 => array( 'flag_id' => 371, 'title' => 'France', 'code' => 'E77'),
        372 => array( 'flag_id' => 372, 'title' => 'Gabon', 'code' => 'R1u'),
        373 => array( 'flag_id' => 373, 'title' => 'Gambia', 'code' => 'TZ6'),
        374 => array( 'flag_id' => 374, 'title' => 'Georgia', 'code' => '8Ou'),
        375 => array( 'flag_id' => 375, 'title' => 'German', 'code' => 'K7e'),
        376 => array( 'flag_id' => 376, 'title' => 'Ghana', 'code' => '6Mr'),
        377 => array( 'flag_id' => 377, 'title' => 'Greece', 'code' => 'kY8'),
        378 => array( 'flag_id' => 378, 'title' => 'Grenada', 'code' => 'yG1'),
        379 => array( 'flag_id' => 379, 'title' => 'Guatemala', 'code' => 'aE8'),
        380 => array( 'flag_id' => 380, 'title' => 'Guinea', 'code' => '6Lm'),
        381 => array( 'flag_id' => 381, 'title' => 'Guinea-Bissau', 'code' => 'I39'),
        382 => array( 'flag_id' => 382, 'title' => 'Guyana', 'code' => 'Mh5'),
        383 => array( 'flag_id' => 383, 'title' => 'Haiti', 'code' => 'Qx7'),
        384 => array( 'flag_id' => 384, 'title' => 'Honduras', 'code' => 'm5Q'),
        385 => array( 'flag_id' => 385, 'title' => 'Hungary ', 'code' => 'OU2'),
        386 => array( 'flag_id' => 386, 'title' => 'Iceland', 'code' => 'Ho8'),
        387 => array( 'flag_id' => 387, 'title' => 'India', 'code' => 'My6'),
        388 => array( 'flag_id' => 388, 'title' => 'Indonesia', 'code' => 'G0m'),
        389 => array( 'flag_id' => 389, 'title' => 'Iran', 'code' => 'Vo7'),
        390 => array( 'flag_id' => 390, 'title' => 'Iraq', 'code' => 'z7I'),
        391 => array( 'flag_id' => 391, 'title' => 'Ireland', 'code' => '5Tr'),
        392 => array( 'flag_id' => 392, 'title' => 'Israel', 'code' => '5KS'),
        393 => array( 'flag_id' => 393, 'title' => 'Italy', 'code' => 'BW7'),
        394 => array( 'flag_id' => 394, 'title' => 'Jamaica', 'code' => 'u6W'),
        395 => array( 'flag_id' => 395, 'title' => 'Japan', 'code' => '4YX'),
        396 => array( 'flag_id' => 396, 'title' => 'Jordan', 'code' => 's2B'),
        397 => array( 'flag_id' => 397, 'title' => 'Kazakhstan', 'code' => 'QA5'),
        398 => array( 'flag_id' => 398, 'title' => 'Kenya', 'code' => 'X3y'),
        399 => array( 'flag_id' => 399, 'title' => 'Kiribati', 'code' => 'l2H'),
        400 => array( 'flag_id' => 400, 'title' => 'Kosovs', 'code' => 'Pb3'),
        401 => array( 'flag_id' => 401, 'title' => 'Kuwait', 'code' => 'P5F'),
        402 => array( 'flag_id' => 402, 'title' => 'Kyrgyzstan', 'code' => 'uP6'),
        403 => array( 'flag_id' => 403, 'title' => 'Laos', 'code' => 'Qy5'),
        404 => array( 'flag_id' => 404, 'title' => 'Latvia', 'code' => 'j1D'),
        405 => array( 'flag_id' => 405, 'title' => 'Lebanon', 'code' => 'Rl2'),
        406 => array( 'flag_id' => 406, 'title' => 'Lesotho', 'code' => 'lB1'),
        407 => array( 'flag_id' => 407, 'title' => 'Liberia', 'code' => '9Qw'),
        408 => array( 'flag_id' => 408, 'title' => 'Libya', 'code' => 'v6I'),
        409 => array( 'flag_id' => 409, 'title' => 'Liechtenstein', 'code' => '2GH'),
        410 => array( 'flag_id' => 410, 'title' => 'Lithuania', 'code' => 'uI6'),
        411 => array( 'flag_id' => 411, 'title' => 'Luxembourg', 'code' => 'EV8'),
        412 => array( 'flag_id' => 412, 'title' => 'Macedonia', 'code' => '6GV'),
        413 => array( 'flag_id' => 413, 'title' => 'Madagascar', 'code' => '4tE'),
        414 => array( 'flag_id' => 414, 'title' => 'Malawi', 'code' => 'O9C'),
        415 => array( 'flag_id' => 415, 'title' => 'Malaysia', 'code' => 'C9k'),
        416 => array( 'flag_id' => 416, 'title' => 'Maldives', 'code' => '1Q3'),
        417 => array( 'flag_id' => 417, 'title' => 'Mali', 'code' => 'Yi5'),
        418 => array( 'flag_id' => 418, 'title' => 'Malta', 'code' => 'N11'),
        419 => array( 'flag_id' => 419, 'title' => 'Marshall Islands', 'code' => 'Z3x'),
        420 => array( 'flag_id' => 420, 'title' => 'Mauritania', 'code' => 'F18'),
        421 => array( 'flag_id' => 421, 'title' => 'Mauritius', 'code' => 'mH4'),
        422 => array( 'flag_id' => 422, 'title' => 'Mexico', 'code' => '8Qb'),
        423 => array( 'flag_id' => 423, 'title' => 'Micronesia', 'code' => 'H6t'),
        424 => array( 'flag_id' => 424, 'title' => 'Moldova', 'code' => 'FD8'),
        425 => array( 'flag_id' => 425, 'title' => 'Monaco', 'code' => 't0X'),
        426 => array( 'flag_id' => 426, 'title' => 'Mongolia', 'code' => 'X8h'),
        427 => array( 'flag_id' => 427, 'title' => 'Montenegro', 'code' => '61A'),
        428 => array( 'flag_id' => 428, 'title' => 'Morocco', 'code' => 'M2e'),
        429 => array( 'flag_id' => 429, 'title' => 'Mozambique', 'code' => 'J7N'),
        430 => array( 'flag_id' => 430, 'title' => 'Myanmar ', 'code' => 'YB9'),
        431 => array( 'flag_id' => 431, 'title' => 'Namibia', 'code' => 'r0H'),
        432 => array( 'flag_id' => 432, 'title' => 'Nauru', 'code' => 'M09'),
        433 => array( 'flag_id' => 433, 'title' => 'Nepal', 'code' => 'E0c'),
        434 => array( 'flag_id' => 434, 'title' => 'Netherlands', 'code' => '8jV'),
        435 => array( 'flag_id' => 435, 'title' => 'New Zealand', 'code' => '0Mi'),
        436 => array( 'flag_id' => 436, 'title' => 'Nicaragua', 'code' => '5dN'),
        437 => array( 'flag_id' => 437, 'title' => 'Niger', 'code' => 'Rj0'),
        438 => array( 'flag_id' => 438, 'title' => 'Nigeria', 'code' => '8oM'),
        439 => array( 'flag_id' => 439, 'title' => 'North Korea', 'code' => '3Yz'),
        440 => array( 'flag_id' => 440, 'title' => 'Norvay', 'code' => '4KE'),
        441 => array( 'flag_id' => 441, 'title' => 'Oman', 'code' => '8NL'),
        442 => array( 'flag_id' => 442, 'title' => 'Pakistan', 'code' => 'n4T'),
        443 => array( 'flag_id' => 443, 'title' => 'Palau', 'code' => '8G2'),
        444 => array( 'flag_id' => 444, 'title' => 'Panama', 'code' => '93O'),
        445 => array( 'flag_id' => 445, 'title' => 'Papua New Guinea', 'code' => 'FD4'),
        446 => array( 'flag_id' => 446, 'title' => 'Paraguay', 'code' => 'y5O'),
        447 => array( 'flag_id' => 447, 'title' => 'Peru', 'code' => '4MJ'),
        448 => array( 'flag_id' => 448, 'title' => 'Philippines', 'code' => '2qL'),
        449 => array( 'flag_id' => 449, 'title' => 'Poland ', 'code' => 'j0R'),
        450 => array( 'flag_id' => 450, 'title' => 'Portugal', 'code' => '0Rq'),
        451 => array( 'flag_id' => 451, 'title' => 'Qatar', 'code' => 'a8S'),
        452 => array( 'flag_id' => 452, 'title' => 'Romania', 'code' => 'nC7'),
        453 => array( 'flag_id' => 453, 'title' => 'Russia', 'code' => 'D1H'),
        454 => array( 'flag_id' => 454, 'title' => 'Rwanda', 'code' => '8UD'),
        455 => array( 'flag_id' => 455, 'title' => 'Saint Kitts and Nevis', 'code' => 'X2d'),
        456 => array( 'flag_id' => 456, 'title' => 'Saint Lucia', 'code' => 'I5e'),
        457 => array( 'flag_id' => 457, 'title' => 'Saint Vincent and the Grenadines', 'code' => '3Kf'),
        458 => array( 'flag_id' => 458, 'title' => 'Samoa', 'code' => '54E'),
        459 => array( 'flag_id' => 459, 'title' => 'San Marino', 'code' => 'K4F'),
        460 => array( 'flag_id' => 460, 'title' => 'Sao Tome and Principe', 'code' => 'cZ9'),
        461 => array( 'flag_id' => 461, 'title' => 'Saudi Arabia', 'code' => 'J06'),
        462 => array( 'flag_id' => 462, 'title' => 'Senegal', 'code' => 'x2O'),
        463 => array( 'flag_id' => 463, 'title' => 'Serbia', 'code' => 'GC6'),
        464 => array( 'flag_id' => 464, 'title' => 'Seychelles', 'code' => 'JE6'),
        465 => array( 'flag_id' => 465, 'title' => 'Sierra Leone', 'code' => 'mS4'),
        466 => array( 'flag_id' => 466, 'title' => 'Singapore', 'code' => 'O6e'),
        467 => array( 'flag_id' => 467, 'title' => 'Slovakia', 'code' => 'Y2i'),
        468 => array( 'flag_id' => 468, 'title' => 'Slovenia', 'code' => 'ZR1'),
        469 => array( 'flag_id' => 469, 'title' => 'Solomon Islands', 'code' => '0U1'),
        470 => array( 'flag_id' => 470, 'title' => 'Somalia', 'code' => '3fH'),
        471 => array( 'flag_id' => 471, 'title' => 'South Africa', 'code' => '7xS'),
        472 => array( 'flag_id' => 472, 'title' => 'South Korea', 'code' => '0W3'),
        473 => array( 'flag_id' => 473, 'title' => 'South Sudan', 'code' => 'H4u'),
        474 => array( 'flag_id' => 474, 'title' => 'Spain', 'code' => 'A5d'),
        475 => array( 'flag_id' => 475, 'title' => 'Sri Lanka', 'code' => '9JL'),
        476 => array( 'flag_id' => 476, 'title' => 'Sudan', 'code' => 'Wh1'),
        477 => array( 'flag_id' => 477, 'title' => 'Suriname', 'code' => '7Rb'),
        478 => array( 'flag_id' => 478, 'title' => 'Swaziland', 'code' => 'f6L'),
        479 => array( 'flag_id' => 479, 'title' => 'Sweden', 'code' => 'oZ3'),
        480 => array( 'flag_id' => 480, 'title' => 'Switzerland', 'code' => '8aW'),
        481 => array( 'flag_id' => 481, 'title' => 'Syria', 'code' => 'UZ9'),
        // 482 => array( 'flag_id' => 482, 'title' => 'Taiwan', 'code' => 'Rg9'),
        482 => array( 'flag_id' => 482, 'title' => 'Taiwan', 'code' => '00T'),
        483 => array( 'flag_id' => 483, 'title' => 'Tajikistan', 'code' => '7Qa'),
        484 => array( 'flag_id' => 484, 'title' => 'Tanzania', 'code' => 'VU7'),
        485 => array( 'flag_id' => 485, 'title' => 'Thailand', 'code' => 'V6r'),
        486 => array( 'flag_id' => 486, 'title' => 'Timor-Leste', 'code' => '52C'),
        487 => array( 'flag_id' => 487, 'title' => 'Togo', 'code' => 'HH3'),
        488 => array( 'flag_id' => 488, 'title' => 'Tonga', 'code' => '8Ox'),
        489 => array( 'flag_id' => 489, 'title' => 'Trinidad and Tobago', 'code' => 'oZ8'),
        490 => array( 'flag_id' => 490, 'title' => 'Tunisia', 'code' => 'pD6'),
        491 => array( 'flag_id' => 491, 'title' => 'Turkey', 'code' => 'YZ9'),
        492 => array( 'flag_id' => 492, 'title' => 'Turkmenistan', 'code' => 'Tm5'),
        493 => array( 'flag_id' => 493, 'title' => 'Tuvalu', 'code' => 'u0Y'),
        494 => array( 'flag_id' => 494, 'title' => 'Uganda', 'code' => 'eJ2'),
        495 => array( 'flag_id' => 495, 'title' => 'Ukraine', 'code' => '2Mg'),
        496 => array( 'flag_id' => 496, 'title' => 'United Arab Emirates', 'code' => 'DT3'),
        497 => array( 'flag_id' => 497, 'title' => 'United Kingdom', 'code' => 'Dw0'),
        498 => array( 'flag_id' => 498, 'title' => 'United States of America', 'code' => 'R04'),
        499 => array( 'flag_id' => 499, 'title' => 'Uruguay', 'code' => 'aL9'),
        500 => array( 'flag_id' => 500, 'title' => 'Uzbekistan', 'code' => 'zJ3'),
        501 => array( 'flag_id' => 501, 'title' => 'Vanuatu', 'code' => 'D0Y'),
        502 => array( 'flag_id' => 502, 'title' => 'Vatican City', 'code' => 'FG2'),
        503 => array( 'flag_id' => 503, 'title' => 'Venezuela', 'code' => 'Eg6'),
        504 => array( 'flag_id' => 504, 'title' => 'Vietnam', 'code' => 'l2A'),
        505 => array( 'flag_id' => 505, 'title' => 'Yemen', 'code' => 'YZ0'),
        506 => array( 'flag_id' => 506, 'title' => 'Zambia', 'code' => '9Be'),
        507 => array( 'flag_id' => 507, 'title' => 'Zimbabwe', 'code' => '80Y'),
        508 => array( 'flag_id' => 508, 'title' => 'Hong Kong', 'code' => '00H'),
        509 => array( 'flag_id' => 509, 'title' => 'Hawaii', 'code' => '00P'),
    );

    var $widgetStyles = [
        'dropdown' => 'Dropdown',
        'list' => 'Vertical list '
    ];

    function __construct()
    {
        $this->api_key = get_option( 'api_key' );
        $this->source_language = get_option( 'source_language' );
        $this->target_languages = get_option( 'target_languages', array() );
        $this->default_language = get_option( 'default_language' );
        $this->target_languages_translations = get_option( 'target_languages_translations', array() );
        $this->target_languages_translations = $this->target_languages_translations ? json_decode($this->target_languages_translations, true) : array();
        $this->style_change_language = get_option( 'style_change_language', array() );
        $this->style_change_flag = get_option( 'style_change_flag', array() );
        $this->style_flag = get_option( 'style_flag', 'rect' );
        $this->style_text = get_option( 'style_text', 'full-text' );
        $this->style_position_vertical = get_option( 'style_position_vertical', 'top' );
        $this->style_position_horizontal = get_option( 'style_position_horizontal', 'left' );
        $this->style_indenting_vertical = get_option( 'style_indenting_vertical', '12' );
        $this->style_indenting_horizontal = get_option( 'style_indenting_horizontal', '24' );
        $this->auto_translate = get_option( 'auto_translate', '1' );
        $this->hide_conveythis_logo = get_option( 'hide_conveythis_logo', '0' );
        $this->translate_media = get_option( 'translate_media', '0' );
        $this->translate_document = get_option( 'translate_document', '0' );
        $this->translate_links = get_option( 'translate_links', '0' );
        $this->change_direction = get_option( 'change_direction', '0' );
        $this->alternate = get_option( 'alternate', '1' );
        $this->accept_language = get_option( 'accept_language', '0' );
        $this->blockpages = get_option( 'blockpages', array() );
        $this->show_javascript = get_option( 'show_javascript', '1' );

        $this->style_position_type = get_option( 'style_position_type', 'fixed' );
        $this->style_position_vertical_custom = get_option( 'style_position_vertical_custom', 'bottom' );
        $this->style_selector_id = get_option( 'style_selector_id', '' );

        $this->url_structure = get_option( 'url_structure', 'regular' );

        $this->style_background_color = get_option( 'style_background_color', '#ffffff' );
        $this->style_hover_color = get_option( 'style_hover_color', '#f6f6f6' );
        $this->style_border_color = get_option( 'style_border_color', '#e0e0e0' );
        $this->style_text_color = get_option( 'style_text_color', '#000000' );
        $this->style_corner_type = get_option( 'style_corner_type', 'cir' );
        $this->style_widget = get_option( 'style_widget', 'dropdown' );

        //
        uasort($this->languages, function($a, $b){
            if(strcmp($a['title_en'], $b['title_en']) > 0)
                return 1;
            else if(strcmp($a['title_en'], $b['title_en']) < 0)
                return -1;
            else
                return 0;
        });
        uasort($this->flags, function($a, $b){
            if(strcmp($a['title'], $b['title']) > 0)
                return 1;
            else if(strcmp($a['title'], $b['title']) < 0)
                return -1;
            else
                return 0;
        });
        //

        $this->blockpages_items = [];

        foreach( $this->blockpages as $blockpage )
        {
            if( !empty( $blockpage ) )
            {
                $page_url = $this->getPageUrl( $blockpage );
                $this->blockpages_items[] = $page_url;
            }
        }



        $plugin = plugin_basename( __FILE__ );

        add_filter( 'plugin_action_links_' . $plugin, array( $this, '_settings_link' ) );
        add_filter( 'plugin_row_meta', array( $this, '_row_meta' ), 10, 2 );
        add_filter( 'wp_nav_menu', array( $this, '_menu_shortcode' ), 20, 2 );

        add_action( 'init', array( $this, '_init' ) );

        add_action( 'update_option', array($this, 'plugin_update_option'), 10, 3 );
        add_action( 'admin_menu', array( $this, '_admin_menu' ) );
        add_action( 'admin_init', array( $this, '_admin_init' ) );
        add_action( 'admin_notices', array( $this, '_admin_notices' ), 10 ) ;

        add_action( 'admin_head-nav-menus.php', array( $this, 'add_nav_menu_meta_boxes' ) );
        add_filter( 'nav_menu_link_attributes', array( $this, 'magellanlinkfilter' ), 10, 3 );

        add_action( 'widgets_init', 'wp_register_widget' );
        add_shortcode('conveythis_switcher', array($this, 'get_conveythis_shortcode'));

        //RankMath sitemap
        add_action( 'parse_query', array( $this, 'rank_math_sitemap_init' ), 0 );
        add_action( 'rank_math/sitemap/url', array( $this, 'sitemap_add_translated_urls' ), 10, 2 );
        //

        //Yoast sitemap
        add_action( 'pre_get_posts', array( $this, 'wpseo_init_sitemap' ), 1 );
        add_action( 'wpseo_sitemap_url', array( $this, 'sitemap_add_translated_urls' ), 10, 2 );
        //

        //SeoPress sitemap
        add_action( 'seopress_sitemaps_urlset', array( $this, 'sitemap_add_xhtml_to_urlset' ), 10, 1 );
        add_action( 'seopress_sitemaps_url', array( $this, 'sitemap_add_translated_urls' ), 10, 2 );

        add_action('wp_ajax_conveythis_clear_all_cache', array($this, 'clearAllCache'));
        add_action('wp_ajax_conveythis_dismiss_all_cache', array($this, 'dismissAllCacheMessages'));

        add_action('pre_post_update', array($this, 'clear_post'), 10, 2);

        if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/') !== false) {
            if (isset($_POST['exclusions'])) {
                $this->updateRules($_POST['exclusions'], 'exclusion');
            }
            if (isset($_POST['glossary'])) {
                $this->updateRules($_POST['glossary'], 'glossary');
            }
            if (isset($_POST['exclusion_blocks'])) {
                $this->updateRules($_POST['exclusion_blocks'], 'exclusion_blocks');
            }

            if (isset($_POST['clear_translate_cache']) && $_POST['clear_translate_cache']) {
                $result = $this->clear_cached_translations(true);
                header( 'Content-Type: application/json', 1 );
                echo json_encode(['clear_cache_translate' => $result]);
                exit();
            }
        }

    }

    public function clear_post($post_id, $post_data)
    {
        $postLink = get_permalink($post_id);
        foreach ($this->target_languages as $targetLanguage) {
            $clearUrl = $this->getTranslateSiteUrl($postLink, $targetLanguage);
            $this->clearPageCache($clearUrl);
        }
    }



    public static function checkCachePlugin()
    {
        $installCachePlugin = false;
        if (function_exists('w3tc_flush_all')
            || class_exists('LiteSpeed_Cache')
            || function_exists('wp_cache_clear_cache')
            || function_exists('wpfc_clear_all_cache')
            || function_exists('rocket_clean_domain')
            || function_exists('hyper_cache_clean')
            || function_exists('sc_cache_flush')
            || class_exists('Cache_Enabler') || has_action('cachify_flush_cache')) {
            $installCachePlugin = true;
        }
        return $installCachePlugin;
    }

    /*
     * Clearing page cache by installed plugin
     */
    public static function clearPageCache($url = '')
    {

        if (strlen($url) > 0) {
            if (function_exists('flush_url')) {
                flush_url($url);
            }

            if (class_exists('LiteSpeed_Cache')) {
                LiteSpeed_Cache::plugin()->purge_url($url);
            }

            if (function_exists('wp_cache_clear_cache')) {

            }

            if (function_exists('wpfc_clear_all_cache')) {

            }

            if (function_exists('rocket_clean_files')) {
                rocket_clean_files($url);
            }

            if (function_exists('hyper_cache_clean')) {

            }

            if (function_exists('sc_cache_flush')) {

            }

            if (class_exists('Cache_Enabler') && method_exists('Cache_Enabler', 'clear_total_cache')) {

            }

            if (has_action('cachify_flush_cache')) {

            }
        }
    }

    public function clearAllCache()
    {
        if ($_POST['conveythis_clear_all_cache'] == true && $_POST['api_key'] == $this->api_key) {
            self::flush_cache_on_activate();
        }
        die( json_encode (['clear' => true]));
    }

    public static function flush_cache_on_activate(){
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        // LiteSpeed
        if (class_exists('LiteSpeed_Cache')) {
            LiteSpeed_Cache::plugin()->purge_all();
        }
        /*if (class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_all')) {
            LiteSpeed_Cache_API::purge_all();
        }*/
        // WP Fastest Cache
        if (function_exists('wpfc_clear_all_cache')) {
            wpfc_clear_all_cache();
        }
        // WP Fastest Cache
        if (function_exists('wpfc_clear_all_cache')) {
            wpfc_clear_all_cache();
        }
        // WP Rocket
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }
        if (function_exists('hyper_cache_clean')) {
            hyper_cache_clean();
        }
        // Simple Cache
        if (function_exists('sc_cache_flush')) {
            sc_cache_flush();
        }
        // W3 Total Cache : w3tc
        if (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        }
        // WP Super Cache : wp-super-cache
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        // WP Fastest Cache
        if (function_exists('wpfc_clear_all_cache')) {
            wpfc_clear_all_cache(true);
        }
        // WPEngine
        if (class_exists('WpeCommon') && method_exists('WpeCommon', 'purge_varnish_cache')) {
            WpeCommon::purge_memcached();
            WpeCommon::clear_maxcdn_cache();
            WpeCommon::purge_varnish_cache();
        }
        // SG Optimizer by Siteground
        if (function_exists('sg_cachepress_purge_cache')) {
            sg_cachepress_purge_cache();
        }
        // Cache Enabler
        if (class_exists('Cache_Enabler') && method_exists('Cache_Enabler', 'clear_total_cache')) {
            Cache_Enabler::clear_total_cache();
        }
        // Comet cache
        if (class_exists('comet_cache') && method_exists('comet_cache', 'clear')) {
            comet_cache::clear();
        }
        // Pagely
        if (class_exists('PagelyCachePurge') && method_exists('PagelyCachePurge', 'purgeAll')) {
            PagelyCachePurge::purgeAll();
        }
        // Autoptimize
        if (class_exists('autoptimizeCache') && method_exists('autoptimizeCache', 'clearall')) {
            autoptimizeCache::clearall();
        }
        // Hummingbird Cache
        if (class_exists('\Hummingbird\WP_Hummingbird') && method_exists('\Hummingbird\WP_Hummingbird', 'flush_cache')) {
            \Hummingbird\WP_Hummingbird::flush_cache();
        }
        // Autoptimize
        if (has_action('cachify_flush_cache')) {
            do_action('cachify_flush_cache');
        }
    }

    public function flush_cache($option){

        if ($option == 'target_languages_translations') {


            if (function_exists('w3tc_flush_all')) {
                w3tc_flush_all();
            }
            if (class_exists('LiteSpeed_Cache')) {
                LiteSpeed_Cache::plugin()->purge_all();
            }
            if (function_exists('wp_cache_clear_cache')) {
                wp_cache_clear_cache();
            }
            if (function_exists('wpfc_clear_all_cache')) {
                wpfc_clear_all_cache();
            }
            if ( function_exists( 'rocket_clean_domain' ) ) {
                rocket_clean_domain();
            }
            if (function_exists('hyper_cache_clean')) {
                hyper_cache_clean();
            }
            if (function_exists('sc_cache_flush')) {
                sc_cache_flush();
            }

            if (class_exists('Cache_Enabler') && method_exists('Cache_Enabler', 'clear_total_cache')) {
                Cache_Enabler::clear_total_cache();
            }
            if ( has_action('cachify_flush_cache') ) {
                do_action('cachify_flush_cache');
            }
        }

    }

    public function dismissAllCacheMessages()
    {
        if ($_POST['dismiss']) {
            $this->dismissNotice('all_cache_notice');
        }
    }

    public function rank_math_sitemap_init(){
        global $wp_query;
        if( !empty($wp_query) ){
            $type = get_query_var( 'sitemap', '' );
            add_filter( "rank_math/sitemap/{$type}_urlset",  array( $this, 'sitemap_add_xhtml_to_urlset' ) );
        }
    }

    public function wpseo_init_sitemap() {
        global $wp_query;
        if ( !empty( $wp_query ) ) {
            $type = get_query_var( 'sitemap', '' );
            add_filter( "wpseo_sitemap_{$type}_urlset",  array( $this, 'sitemap_add_xhtml_to_urlset' ) );
        }
    }

    public function sitemap_add_xhtml_to_urlset( $urlset ){
        $urlset = str_replace(  '<urlset', '<urlset xmlns:xhtml="http://www.w3.org/1999/xhtml" ', $urlset);
        return $urlset;
    }

    public function sitemap_add_translated_urls( $output, $url ){

        if(in_array($url['loc'], $this->blockpages)) // no need to add translated url for blocked pages
            return $output;

        $alternate = "";
        $translatedOutputUrls = array();

        foreach($this->target_languages as $language){

            $site_url = home_url();

            $site_url = str_replace("https://","",$site_url);
            $site_url = str_replace("http://","",$site_url);

            if(!empty($this->url_structure) && $this->url_structure == "subdomain")
                $translatedUrl = str_replace($site_url, $language.".".$site_url, $url['loc']);
            else
                $translatedUrl = str_replace($site_url, $site_url."/".$language, $url['loc']);


            $loc = "\t\t<loc>".$translatedUrl."</loc>\n";
            $lasmod = !empty($url['mod']) ? "\t\t<lastmod>".date('c', strtotime($url['mod']))."</lastmod>\n" : "";
            $images = "";
            if( isset($url['images']) && is_array($url['images']) ){
                foreach ($url['images'] as $image) {
                    $images .= "\t\t<image:image><image:loc>".$image['src']."</image:loc></image:image>\n";
                }
            }

            $translatedOutputUrls[] = "\t<url>\n".$loc.$lasmod.$images."\t</url>\n";
            $alternate .= "\t<xhtml:link rel='alternate' hreflang='".$language."' href='".$translatedUrl."' />\n\t";
        }

        // add source language to alternate
        $alternate .= "\t<xhtml:link rel='alternate' hreflang='".$this->source_language."' href='".$url['loc']."' />\n\t";

        //add alternate to translated url
        foreach ( $translatedOutputUrls as &$value){
            $value = str_replace("</url>", $alternate."</url>", $value);
        }

        //add alternate to source url
        $newOutput = str_replace("</url>", $alternate."</url>", $output);

        $translatedOutput = implode("",$translatedOutputUrls);

        return $newOutput.$translatedOutput;
    }

    public function magellanlinkfilter( $attr, $post, $menu )
    {
        preg_match( '/\[ConveyThis_(.*)\]/', $post->title, $matches );

        if( !empty( $matches ) )
        {
            $language = $this->searchLanguage( $matches[1] );

            if( !empty( $language ) )
            {
                if( !empty( $this->language_code ) )
                {
                    if( $language['code2'] === $this->source_language )
                    {
                        $language = $this->searchLanguage( $this->language_code );
                    }

                    else if( $language['code2'] === $this->language_code )
                    {
                        $language = $this->searchLanguage( $this->source_language );
                    }
                }

                //

                $site_url = $this->site_url;
                $prefix = $this->getPageUrl( $site_url );

                if(!empty($this->url_structure) && $this->url_structure == "subdomain")
                    $location = $this->getSubDomainLocation( $language['code2'] );
                else
                    $location = $this->getLocation( $prefix, $language['code2'] );

                $icon = $this->genIcon( $language['language_id'], $language['flag'] );
                $attr['translate'] = 'no';
                $attr['href'] = $location;
                $attr['class'] = "conveythis-no-translate notranslate";

                if( $this->style_text === 'full-text' )
                {
                    $post->title = $icon . $language['title'];
                }
                if( $this->style_text === 'short-text' )
                {
                    $post->title = $icon . strtoupper( $language['code3'] );
                }
                if( $this->style_text === 'without-text' )
                {
                    $post->title = $icon;
                }

            }
        }

        return $attr;
    }

    public function genIcon( $language_id, $flag )
    {
        $i = 0;

        while( $i < 5 )
        {
            if( !empty( $this->style_change_language[$i] ) && $this->style_change_language[$i] == $language_id )
            {
                $flag = $this->style_change_flag[$i];
            }
            $i++;
        }

        //

        $icon = '';

        if( $this->style_flag === 'rect' )
        {
            $icon = '<span style="height: 20px; width: 30px; background-image: url(\'//cdn.conveythis.com/images/flags/v3/rectangular/' . $flag . '.png\'); display: inline-block; background-size: contain; background-position: 50% 50%; background-repeat: no-repeat; background-color: transparent; margin-right: 10px; vertical-align: middle;"></span>';
        }

        if( $this->style_flag === 'sqr' )
        {
            $icon = '<span style="height: 24px; width: 24px; background-image: url(\'//cdn.conveythis.com/images/flags/v3/square/' . $flag . '.png\'); display: inline-block; background-size: contain; background-position: 50% 50%; background-repeat: no-repeat; background-color: transparent; margin-right: 10px; vertical-align: middle;"></span>';
        }

        if( $this->style_flag === 'cir' )
        {
            $icon = '<span style="height: 24px; width: 24px; background-image: url(\'//cdn.conveythis.com/images/flags/v3/round/' . $flag . '.png\'); display: inline-block; background-size: contain; background-position: 50% 50%; background-repeat: no-repeat; background-color: transparent; margin-right: 10px; vertical-align: middle;"></span>';
        }

        if( $this->style_flag === 'without-flag' )
        {
            $icon = '';
        }

        return $icon;
    }

    public function _menu_shortcode( $menu, $args )
    {
        return do_shortcode( $menu );
    }

    public function add_nav_menu_meta_boxes()
    {
        add_meta_box( 'conveythis_nav_link', __( 'ConveyThis', 'conveythis-translate' ), array( $this, 'nav_menu_links' ), 'nav-menus', 'side', 'low' );
    }

    public function nav_menu_links()
    {
        $languages = array();

        if( !empty( $this->language_code ) )
        {
            $current_language_code = $this->language_code;
        }

        else
        {
            $current_language_code = $this->source_language;
        }

        //

        $language = $this->searchLanguage( $current_language_code );

        if( !empty( $language ) )
        {
            $languages[] = array(
                'id' => $language['language_id'],
                'title' => $language['title'],
                'title_en' => $language['title_en'],
            );
        }

        if( !empty( $this->language_code ) )
        {
            $language = $this->searchLanguage( $this->source_language );

            if( !empty( $language ) )
            {
                $languages[] = array(
                    'id' => $language['language_id'],
                    'title' => $language['title'],
                    'title_en' => $language['title_en'],
                );
            }
        }

        foreach( $this->target_languages as $language_code )
        {
            $language = $this->searchLanguage( $language_code );

            if( !empty( $language ) )
            {
                if( $current_language_code != $language['code2'] )
                {
                    $languages[] = array(
                        'id' => $language['language_id'],
                        'title' => $language['title'],
                        'title_en' => $language['title_en'],
                    );
                }
            }
        }
        ?>
        <div id="posttype-conveythis-languages" class="posttypediv">
            <div id="tabs-panel-conveythis-endpoints" class="tabs-panel tabs-panel-active">
                <ul id="conveythis-endpoints-checklist" class="categorychecklist form-no-clear">

                    <?php foreach( $languages as $index => $language ) : ?>
                        <li>
                            <label class="menu-item-title">
                                <input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo  esc_attr( $index + 1 ); ?>][menu-item-object-id]" value="<?php echo  esc_attr( $index + 1 ); ?>" /> <?php echo  esc_html( $language['title_en'] ); ?>
                            </label>
                            <input type="hidden" class="menu-item-type" name="menu-item[<?php echo  esc_attr( $index + 1 ); ?>][menu-item-type]" value="custom" />
                            <input type="hidden" class="menu-item-title" name="menu-item[<?php echo  esc_attr( $index + 1 ); ?>][menu-item-title]" value="[ConveyThis_<?php echo  esc_html( $language['title_en'] ); ?>]" />

                            <input type="hidden" class="menu-item-classes" name="menu-item[<?php echo  esc_attr( $index + 1 ); ?>][menu-item-classes]" />
                        </li>

                    <?php endforeach; ?>

                </ul>
            </div>
            <p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo  esc_url( admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-conveythis-languages' ) ); ?>" class="select-all"><?php esc_html_e( 'Select all', 'conveythis-translate' ); ?></a>
				</span>
                <span class="add-to-menu">
					<button type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'conveythis-translate' ); ?>" name="add-post-type-menu-item" id="submit-posttype-conveythis-languages"><?php esc_html_e( 'Add to menu', 'conveythis-translate' ); ?></button>
					<span class="spinner"></span>
				</span>
            </p>
        </div>
        <?php
    }

    function _row_meta( $links, $file )
    {
        $plugin = plugin_basename( __FILE__ );

        if( $plugin == $file )
        {
            $links[] = '<a href="https://www.conveythis.com/help-center/support-and-resources/?utm_source=widget&utm_medium=wordpress" target="_blank">' . __( 'FAQ', 'conveythis-translate' ) . '</a>';
            $links[] = '<a href="https://wordpress.org/support/plugin/conveythis-translate" target="_blank">' . __( 'Support', 'conveythis-translate' ) . '</a>';
            $links[] = '<a href="https://wordpress.org/plugins/conveythis-translate/#reviews" target="_blank">' . __( 'Rate this plugin', 'conveythis-translate' ) . '</a>';

        }
        return $links;
    }

    function _settings_link( $links )
    {
        array_push( $links, '<a href="options-general.php?page=convey_this">' . __( 'Settings', 'conveythis-translate' ) . '</a>' );

        return $links;
    }

    function _admin_menu()
    {
        add_menu_page('ConveyThis Settings', 'ConveyThis', 'manage_options', 'convey_this', array( $this, 'pluginOptions' ), 'dashicons-translation' );
    }

    function _admin_notices()
    {
        if( empty( $this->api_key ) || empty($this->source_language) || empty($this->target_languages) )
        {
            ?>
            <div class="error settings-error notice is-dismissible" style="background: #CFE1FF;padding: 0px 0 0 25px;">

                <h3 style="font-weight: bolder;color: #1A1A1A;font-size: 1.3em;margin: 1em 0;">
                    <?php echo  __( 'ConveyThis ', 'conveythis-translate' ); ?><a href="<?php echo  admin_url('admin.php') . '?page=convey_this'; ?>" style="color: #1A4CAF;"><?php echo  __( 'plugin', 'conveythis-translate' ); ?></a>
                    <?php echo  __( 'installed but not yet configured.', 'conveythis-translate' ); ?>
                    <?php if( isset($_REQUEST['page']) && $_REQUEST['page'] != 'convey_this' ): ?>
                        <?php echo  __( 'You need to configure ConveyThis plugin here:', 'conveythis-translate' ); ?>
                        <a href="<?php echo  admin_url('options-general.php') . '?page=convey_this'; ?>"><?php echo  __( 'configuration page', 'conveythis-translate' ); ?></a>
                    <?php endif; ?>
                </h3>
                <div style=" margin: 15px 0 10px 0;">
                    <img style="vertical-align: inherit;" src="<?php echo CONVEY_PLUGIN_PATH;?>images/conveythis-logo-vertical-blue.png" alt="ConveyThis"></a>
                    <ul style="display: inline-block;padding: 0;margin-bottom: 0;">
                        <?php if (empty($this->api_key)): ?>
                            <ol style="padding: 0;">You can get api key with faster registration</ol>
                        <?php endif;?>
                        <?php if (empty($this->source_language)): ?>
                            <ol style="padding: 0;">You can change the original language or keep the default (<?php echo get_bloginfo("language");?>)</ol>
                        <?php endif;?>
                        <?php if (empty($this->target_languages)): ?>
                            <ol style="padding: 0;">You need to choose target languages</p></ol>
                        <?php endif;?>
                    </ul>
                </div>
            </div>
            <?php
        }

        if( !extension_loaded('xml') )
        {
            ?>
            <div class="error settings-error notice is-dismissible">
                <p>
                    <?php echo  __( 'Plugin requires installing php-xml extension.', 'conveythis-translate' ); ?>
                </p>
            </div>
            <?php
        }
    }

    function _admin_init()
    {
        register_setting( 'my-plugin-settings-group', 'api_key' );
        register_setting( 'my-plugin-settings-group', 'source_language' );
        register_setting( 'my-plugin-settings-group', 'target_languages', array( $this, '_check_target_languages' ) );
        register_setting( 'my-plugin-settings-group', 'target_languages_translations' );
        register_setting( 'my-plugin-settings-group', 'default_language' );

        register_setting( 'my-plugin-settings-group', 'style_change_language', array( $this, '_check_style_change_language' ) );
        register_setting( 'my-plugin-settings-group', 'style_change_flag', array( $this, '_check_style_change_flag' ) );
        register_setting( 'my-plugin-settings-group', 'style_flag' );
        register_setting( 'my-plugin-settings-group', 'style_text' );
        register_setting( 'my-plugin-settings-group', 'style_position_vertical' );
        register_setting( 'my-plugin-settings-group', 'style_position_horizontal' );
        register_setting( 'my-plugin-settings-group', 'style_indenting_vertical' );
        register_setting( 'my-plugin-settings-group', 'style_indenting_horizontal' );
        register_setting( 'my-plugin-settings-group', 'auto_translate' );
        register_setting( 'my-plugin-settings-group', 'hide_conveythis_logo' );
        register_setting( 'my-plugin-settings-group', 'translate_media' );
        register_setting( 'my-plugin-settings-group', 'translate_document' );
        register_setting( 'my-plugin-settings-group', 'translate_links' );
        register_setting( 'my-plugin-settings-group', 'change_direction' );

        register_setting( 'my-plugin-settings-group', 'alternate' );
        register_setting( 'my-plugin-settings-group', 'accept_language' );
        register_setting( 'my-plugin-settings-group', 'blockpages', array( $this, '_check_blockpages' ) );
        register_setting( 'my-plugin-settings-group', 'show_javascript' );

        register_setting( 'my-plugin-settings-group', 'style_position_type' );
        register_setting( 'my-plugin-settings-group', 'style_position_vertical_custom' );
        register_setting( 'my-plugin-settings-group', 'style_selector_id' );

        register_setting( 'my-plugin-settings-group', 'url_structure' );

        register_setting( 'my-plugin-settings-group', 'style_background_color' );
        register_setting( 'my-plugin-settings-group', 'style_hover_color' );
        register_setting( 'my-plugin-settings-group', 'style_border_color' );
        register_setting( 'my-plugin-settings-group', 'style_text_color' );
        register_setting( 'my-plugin-settings-group', 'style_corner_type' );
        register_setting( 'my-plugin-settings-group', 'style_widget' );

        if( !empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'convey_this' )
        {
                if( !empty( $this->api_key ) )
                {

                    if (($key = array_search($this->source_language, $this->target_languages)) !== false) { //remove source_language from target_languages
                        unset($this->target_languages[$key]);
                    }

                    $this->send( 'PUT', '/website/update/', array(
                        'referrer' => '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                        'source_language' => $this->source_language ?: 'en',
                        'target_languages' => $this->target_languages ?: ['en'],
                        'accept_language' => $this->accept_language,	// Option
                        'blockpages' => $this->blockpages_items,
                        'technology' => 'wordpress'
                    ));

                    $this->exclusions = $this->send(  'GET', '/admin/account/domain/pages/excluded/?referrer='. urlencode($_SERVER['HTTP_HOST']) );
                    $this->glossary = $this->send(  'GET', '/admin/account/domain/pages/glossary/?referrer='. urlencode($_SERVER['HTTP_HOST']) );
                    $this->exclusion_blocks = $this->send(  'GET', '/admin/account/domain/excluded/blocks/?referrer='. urlencode($_SERVER['HTTP_HOST']) );
                }
        }
    }

    function _check_style_change_language( $value )
    {
        if( !is_array( $value ) )
        {
            return array();
        }

        return $value;
    }

    function _check_style_change_flag( $value )
    {
        if( !is_array( $value ) )
        {
            return array();
        }

        return $value;
    }

    function _check_blockpages( $value )
    {
        if( !is_array( $value ) )
        {
            return array();
        }

        return $value;
    }

    function _check_target_languages( $value )
    {
        if( !empty( $value ) )
        {
            $target_languages = array();
            $language_codes = explode( ',', $value );

            foreach( $language_codes as $language_code )
            {
                $language = $this->searchLanguage( $language_code );

                if( !empty( $language ) )
                {
                    $target_languages[] = $language['code2'];
                }
            }
            return $target_languages;
        }

        else
        {
            return array();
        }
    }

    public function searchLanguage( $value )
    {
        foreach( $this->languages as $language )
        {
            if( $value === $language['code2'] || $value === $language['title_en'] )
            {
                return $language;
            }
        }
    }

    function getPageUrl( $str )
    {
        $n = 0;
        $length = strlen( $str );
        $buffer = '';
        $step = 0;

        while( $n < $length )
        {
            if( $str[$n] === '/' )
            {
                if( $step === 1 ) $step = 2;

                if( $step === 0 )
                {
                    $buffer = '/';
                    $step = 1;
                }
            }

            else
            {
                if( $step === 2 )
                {
                    $buffer = '';
                    $step = 0;
                }

                if( $step === 1 ) $step = 3;
            }

            if( $str[$n] === '?' || $str[$n] === '#' ) break;
            if( $step === 3 ) $buffer .= $str[$n];

            $n++;
        }

        $buffer = trim( $buffer );
        $buffer = rtrim( $buffer, '/' );

        if( empty( $buffer ) )
        {
            $buffer = '/';
        }
        return rtrim( $buffer, '/' ) . '/';
    }

    function getPageHost($url)
    {
        $urlData = parse_url( $url );
        $host = isset( $urlData['host'] )?trim( preg_replace('/^www\./', '', $urlData['host'] )) : null;
        return $host;
    }

    function _init()
    {
        if (strpos($_SERVER["REQUEST_URI"], '/wp-json/') !== false) {
            return;
        }

        if (strpos($_SERVER["REQUEST_URI"], '/conveythis-404/') !== false) {
            get_template_part( 404 );
            return;
        }

        $this->site_url = home_url();
        $this->site_host = $this->getPageHost( $this->site_url );
        $this->site_prefix = $this->getPageUrl( $this->site_url );
        $this->referrer = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        if( !is_admin() )
        {
            if(empty($this->url_structure) || $this->url_structure != "subdomain"){ // no need to do anything with subdomains
                if( $this->auto_translate && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ){

                    if( class_exists('Locale') ){
                        $browserLanguage = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
                        $browserLanguage = substr($browserLanguage, 0, 2);
                    }else{
                        $browserLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                    }

                    if (in_array($browserLanguage, $this->target_languages)) {

                        session_start();
                        if (empty($_SESSION['conveythis-autoredirected'])){

                            $_SESSION['conveythis-autoredirected'] = true;


                            $preventAutoRedirect = false;
                            foreach ($this->target_languages as $key => $language) {	//check if already contains translate language prefix

                                if(strpos($_SERVER["REQUEST_URI"], '/'.$language.'/') !== false
                                    && strpos($_SERVER["REQUEST_URI"], '/'.$language.'/') === 0){

                                    $preventAutoRedirect = true;
                                }
                            }

                            if(!$preventAutoRedirect){

                                $location = $this->getLocation($this->site_prefix, $browserLanguage);
                                header("Location: ".$location);
                                die();
                            }
                        }
                    }

                }

                if( !empty( $this->target_languages ) )
                {

                    $tempRequestUri = $_SERVER["REQUEST_URI"];
                    if(substr($tempRequestUri, -1) != "/")
                        $tempRequestUri .= "/";

                    //$this->target_languages_translations

                    preg_match( '/^('.str_replace( '/', '\/', $this->site_prefix ).'([^\/]+)\/)(.*)/', $tempRequestUri, $matches );

                    if( !empty( $matches ) )
                    {
                        $this->language_code = array_search( urldecode(trim($matches[2])), $this->target_languages_translations );
                    }

                    if ( !$this->language_code ) {

                        preg_match( '/^('.str_replace( '/', '\/', $this->site_prefix ).'('. implode( '|', $this->target_languages ) .')\/)(.*)/', $tempRequestUri, $matches );

                        if( !empty( $matches ) ) {
                            $this->language_code = esc_attr($matches[2]);
                        }
                    }
                    if ( !in_array( $this->default_language, $this->target_languages ))
                    {
                        $this->default_language = '';
                    }
                    if ( !$this->language_code && strpos($_SERVER['REQUEST_URI'], 'wp-login') === false && strpos($_SERVER['REQUEST_URI'], 'wp-admin') === false) {
                        if (!isset($_SERVER['HTTP_REFERER']) || !$_SERVER['HTTP_REFERER'] || $this->site_host != $this->getPageHost($_SERVER['HTTP_REFERER'])) {
                            $this->language_code = isset($this->target_languages_translations[$this->default_language]) ? $this->target_languages_translations[$this->default_language] : $this->default_language;
                        }
                        if ( $this->language_code ) {
                            $translated_slug = $this->find_translation($_SERVER['REQUEST_URI'], $this->source_language, $this->default_language, '//'. $_SERVER['HTTP_HOST']);

                            if ($translated_slug) {
                                $_SERVER['REQUEST_URI'] = $translated_slug;
                            }
                            header('Location: /' . $this->language_code . $_SERVER["REQUEST_URI"], true, 302);
                            exit();
                        }
                    }

                    if( $this->language_code )
                    {

                        $tmp = esc_attr($matches[1]);
                        $origin = $_SERVER["REQUEST_URI"];
                        $_SERVER["REQUEST_URI"] = esc_url( substr_replace( $_SERVER["REQUEST_URI"], $this->site_prefix, 0, strlen( $tmp ) ) );
                        $this->referrer = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                        if (trim($matches[3]) && $this->translate_links) {
                            $slug = '/'. urldecode(trim($matches[3]));
                            $original_slug = $this->find_original_slug($slug, $this->source_language, $this->language_code, $this->referrer);
                            if ( $original_slug ) {
                                $_SERVER["REQUEST_URI"] = preg_replace('/\/' . preg_quote($matches[3], '/') . '$/', $original_slug, $_SERVER["REQUEST_URI"]);
                                $this->referrer = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                            }
                        }

                        $page_url = $this->getPageUrl( $this->referrer );

                        if( in_array( $page_url, $this->blockpages_items ) )
                        {
                            $_SERVER["REQUEST_URI"] = $origin;
                            $this->language_code = null;
                        }

                        if(preg_match("/\/(feed|wp-json)\//", $page_url)){	//prevent translation of RSS and wp-json
                            $this->language_code = null;
                        }
                    }
                }

                //

                if( !empty( $this->source_language ) && !empty( $this->target_languages ) )
                {
                    $page_url = $this->getPageUrl( $this->referrer );

                    if( !in_array( $page_url, $this->blockpages_items ) )
                    {
                        // if( !empty( $this->show_javascript ) )
                        // {
                        $this->getCurrentPlan();
                        add_action( 'wp_footer', array( $this, '_inline_script' ) );
                        // }
                    }
                }

                if( !empty( $this->alternate ) )
                {
                    add_action( 'wp_head', array( $this, '_alternate' ), 0 );

                    if( !empty( $this->language_code ) )
                    {
                        add_filter( 'locale', function( $value ) {
                            return $this->language_code;
                        });
                    }

                    else
                    {
                        add_filter( 'locale', function( $value ) {
                            $langs = explode( '_', $value );
                            return $langs[0];
                        });
                    }
                }

                //~ if( !empty( $this->accept_language ) && empty( $this->language_code ) )
                //~ {
                //~ if( !empty( $_SERVER['REQUEST_URI'] ) && !empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) )
                //~ {
                //~ $this->languageAccept( $_SERVER['REQUEST_URI'], $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
                //~ }
                //~ }
                ob_start( array( $this, '_translatePage' ) );
            }else{
                if( !empty( $this->source_language ) && !empty( $this->target_languages ) ){
                    $this->getCurrentPlan();
                    if( !empty( $this->alternate ) )
                        add_action( 'wp_head', array( $this, '_alternate' ), 0 );
                    add_action( 'wp_footer', array( $this, '_inline_script' ) );
                }
            }
        } else {
            new conveythis_admin_notices();
        }
    }

    function getCurrentPlan(){
        $domain_name = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : '';
        $response = $this->httpRequest("/website/code/get?api_key=".$this->api_key."&domain_name=".$domain_name."&referer=". base64_encode($_SERVER["REQUEST_URI"]), array(
            'method' => "GET"
        ));
        $responseBody = wp_remote_retrieve_body( $response );

        if(!empty($responseBody)){
            $json = json_decode($responseBody);
            if(!empty($json->code)){
                if(strpos($json->code, "conveythis_trial_expired") !== false){
                    $this->plan = 'trial-expired';
                }else{
                    $this->plan = 'paid';
                }
                if (preg_match('/no_translate_element_ids:\s*(\[.+\])/U', $json->code, $matches))  {
                    $this->exclusion_block_ids = json_decode($matches[1]);
                }
                if (preg_match('/is_exceeded:/U', $json->code, $matches))  {
                    $this->exceeded = true;
                }
            }else{
                $this->show_widget = false;
            }
        }
    }

    public function languageAccept( $data, $value )
    {
        $languages = explode( ',', $value );

        if( !empty( $languages ) )
        {
            foreach( $languages as $language )
            {
                $tmp = explode( ';', $language );
                $code = explode( '-', $tmp[0] );

                if( in_array( $code[0], $this->target_languages ) )
                {
                    $location = $this->replaceLink( $data, $code[0] );

                    header('Location: ' . $location );
                    break;
                }
            }
        }
    }

    public function _alternate()
    {
        $site_url_parts = parse_url(home_url());
        $site_domain = $site_url_parts["scheme"]."://".$site_url_parts["host"];
        $site_url = home_url();
        $prefix = $this->getPageUrl( $site_url );

        if(!empty($this->url_structure) && $this->url_structure == "subdomain"){
            $location = $this->getSubDomainLocation( $this->source_language );
            echo '<link rel="alternate" href="' . esc_attr($location) .'" hreflang="x-default">';
        }else{
            $location = $this->getLocation( $prefix, $this->source_language );
            echo '<link rel="alternate" href="' . esc_attr($site_domain . $location) .'" hreflang="x-default">';
        }
        echo "\n";

        $data = array_merge( $this->target_languages, array( $this->source_language ) );

        foreach( $data as $value )
        {
            $language = $this->searchLanguage( $value );

            if( !empty( $language ) )
            {
                if(!empty($this->url_structure) && $this->url_structure == "subdomain"){
                    $location = $this->getSubDomainLocation( $language['code2'] );
                    echo '<link rel="alternate" href="'. esc_attr($location) .'" hreflang="'. esc_attr( $language['code2'] ) .'">';
                    header('Link:<'.esc_attr($location).'>; rel="alternate"; hreflang="'.esc_attr( $language['code2'] ).'"', false);
                }else{
                    $location = $this->getLocation( $prefix, $language['code2'] );
                    echo '<link rel="alternate" href="' . esc_attr($site_domain . $location) .'" hreflang="'. esc_attr( $language['code2'] ) .'">';
                    header('Link:<'.esc_attr($site_domain . $location).'>; rel="alternate"; hreflang="'.esc_attr( $language['code2'] ).'"', false);
                }
            }
            echo "\n";
        }
    }

    public function getLocation( $prefix, $language_code )
    {
        if( $this->source_language == $language_code )
        {
            return $_SERVER["REQUEST_URI"];
        }

        else
        {
            if ( isset( $this->target_languages_translations[$language_code] ) ) {
                $language_code = $this->target_languages_translations[$language_code];
            }

            if(strpos($_SERVER["REQUEST_URI"], '/'.$language_code.'/') !== false
                && strpos($_SERVER["REQUEST_URI"], '/'.$language_code.'/') === 0){ //check if already contains language prefix

                return $_SERVER["REQUEST_URI"];
            }else{

                return substr_replace( $_SERVER["REQUEST_URI"], $prefix . '' . $language_code . '/', 0, strlen( $prefix ) );
            }
        }
    }

    public function getSubDomainLocation( $language_code )
    {
        if( $this->source_language == $language_code )
        {
            return $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        }

        else
        {
            return $_SERVER["REQUEST_SCHEME"]."://".$language_code.".".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        }
    }

    function pluginOptions()
    {
        if( !current_user_can( 'manage_options' ) )
        {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        require_once( 'settings-index.php' );
    }

    public function _inline_script()
    {
        if (is_404()) {
            return;
        }
        if (!$this->show_widget) {
            return;
        }
        $site_url = $this->site_url;
        $prefix = $this->getPageUrl( $site_url );

        //

        $languages = array();

        if( !empty( $this->language_code ) )
        {
            $current_language_code = $this->language_code;
        }

        else
        {
            $current_language_code = $this->source_language;
        }

        //

        $language = $this->searchLanguage( $current_language_code );

        if( !empty( $language ) )
        {
            if(!empty($this->url_structure) && $this->url_structure == "subdomain")
                $location = $this->getSubDomainLocation( $language['code2'] );
            else
                $location = $this->getLocation( $prefix, $language['code2'] );

            $languages[] = '{"id":"'. esc_attr( $language['language_id'] ) .'", "location":"'. esc_attr( $location ) .'", "active":true}';

        }

        //

        if( !empty( $this->language_code ) )
        {
            $language = $this->searchLanguage( $this->source_language );

            if( !empty( $language ) )
            {
                if(!empty($this->url_structure) && $this->url_structure == "subdomain")
                    $location = $this->getSubDomainLocation( $language['code2'] );
                else
                    $location = $this->getLocation( $prefix, $language['code2'] );

                $languages[] = '{"id":"'. esc_attr( $language['language_id'] ) .'", "location":"'. esc_attr( $location ) .'", "active":false}';
            }
        }

        if (($key = array_search($this->source_language, $this->target_languages)) !== false) { //remove source_language from target_languages
            unset($this->target_languages[$key]);
        }

        foreach( $this->target_languages as $language_code )
        {
            $language = $this->searchLanguage( $language_code );

            if( !empty( $language ) )
            {
                if( $current_language_code != $language['code2'] )
                {
                    if(!empty($this->url_structure) && $this->url_structure == "subdomain")
                        $location = $this->getSubDomainLocation( $language['code2'] );
                    else
                        $location = $this->getLocation( $prefix, $language['code2'] );

                    $languages[] = '{"id":"'. esc_attr( $language['language_id'] ) .'", "location":"'. esc_attr( $location ) .'", "active":false}';
                }
            }
        }

        //

        $source_language_id = 0;

        if( !empty( $this->source_language ) )
        {
            $language = $this->searchLanguage( $this->source_language );

            if( !empty( $language ) )
            {
                $source_language_id = $language['language_id'];
            }
        }

        //

        $i = 0;

        $temp = array();

        while( $i < 5 )
        {
            if( !empty( $this->style_change_language[$i] ) )
            {
                $temp[] = '"' . $this->style_change_language[$i] . '":"' . $this->style_change_flag[$i] . '"';
            }
            $i++;
        }

        $change = '{' . implode( ',', $temp ) .'}';

        //

        if($this->style_position_type == 'custom' && $this->style_selector_id != '') {
            if ($this->style_position_vertical_custom == 'top') {
                $positionTop = 50;
                $positionBottom = "null";
            } else {
                $positionTop = "null";
                $positionBottom = 0;
            }

            $positionLeft  = "null";
            $positionRight = 25;
            $styleSelectorId = $this->style_selector_id ?: null;
        }else{
            if ($this->style_position_vertical == 'top') {
                $positionTop = $this->style_indenting_vertical ?: 0;
                $positionBottom = "null";
            } else {
                $positionTop = "null";
                $positionBottom = $this->style_indenting_vertical ?: 0;
            }
            if ($this->style_position_horizontal == 'right') {
                $positionRight = !is_null($this->style_indenting_horizontal) ? $this->style_indenting_horizontal : 24;
                $positionLeft = "null";
            } else {
                $positionRight = "null";
                $positionLeft = !is_null($this->style_indenting_horizontal) ? $this->style_indenting_horizontal : 24;
            }
            $styleSelectorId = null;
        }

        //

        if ($this->plan == 'trial-expired'){
            wp_enqueue_script('conveythis-trial-expired', plugins_url('js/trial-expired.js',__FILE__));
            return;
        }

        if (!empty($this->api_key)) {
            wp_enqueue_script('conveythis-notranslate', plugins_url('js/notranslate.js', __FILE__));
            wp_enqueue_script('conveythis-conveythis', CONVEYTHIS_JAVASCRIPT_PLUGIN_URL . "/conveythis.js", [], 155);
            wp_enqueue_script('conveythis-translate', CONVEYTHIS_JAVASCRIPT_PLUGIN_URL . "/translate.js");
            if (!is_admin() && !empty($this->language_code)) {
                //wp_enqueue_script('conveythis-update-cache', plugins_url('js/update-cache.js', __FILE__));
            }


            $initScript = 'document.addEventListener("DOMContentLoaded", function(e) {';
            $initScript .= 'conveythis.init({';
            $initScript .= 'change:' . $change . ',';
            $initScript .= 'icon:"' . esc_attr($this->style_flag) . '",';
            $initScript .= 'text:"' . esc_attr($this->style_text) . '",';
            $initScript .= 'positionTop:' . esc_attr($positionTop) . ',';
            $initScript .= 'positionBottom:' . esc_attr($positionBottom) . ',';
            $initScript .= 'positionLeft:' . esc_attr($positionLeft) . ',';
            $initScript .= 'positionRight:' . esc_attr($positionRight) . ',';
            $initScript .= 'languages:[' . implode(', ', $languages) . '],';
            $initScript .= 'api_key:"' . esc_attr($this->api_key) . '",';
            $initScript .= 'source_language_id:"' . esc_attr($source_language_id) . '",';
            $initScript .= 'auto_translate:' . esc_attr($this->auto_translate) . ',';
            $initScript .= 'hide_conveythis_logo:' . esc_attr($this->hide_conveythis_logo) . ',';
            $initScript .= 'translate_media:' . esc_attr($this->translate_media) . ',';
            $initScript .= 'translate_document:' . esc_attr($this->translate_document) . ',';
            $initScript .= 'translate_links:' . esc_attr($this->translate_links) . ',';
            $initScript .= 'change_direction:' . esc_attr($this->change_direction) . ',';
            $initScript .= 'php_plugin_cur_lang:"' . $this->searchLanguage($current_language_code)['language_id'] . '",';
            $initScript .= 'background_color:"' . esc_attr($this->style_background_color) . '",';
            $initScript .= 'hover_color:"' . esc_attr($this->style_hover_color) . '",';
            $initScript .= 'border_color:"' . esc_attr($this->style_border_color) . '",';
            $initScript .= 'text_color:"' . esc_attr($this->style_text_color) . '",';
            $initScript .= 'corner_type:"' . esc_attr($this->style_corner_type) . '",';
            $initScript .= 'style_widget:"' . esc_attr($this->style_widget) . '",';

            if (isset($styleSelectorId)) {
                $initScript .= 'selector: "' . $styleSelectorId . '",';
            }
            if (empty($this->show_javascript)) {
                $initScript .= 'hide_conveythis_button: 1,';
            }
            if (isset($this->url_structure) && $this->url_structure == "subdomain") {
                $initScript .= 'is_subdomain: 1,';
            }
            if ( $this->exceeded ) {
                $initScript .= 'is_exceeded: 1,';
            }

            $initScript .= '});';
            $initScript .= '});';

            wp_add_inline_script('conveythis-translate', $initScript);
        }
    }


    function DOMinnerHTML(DOMNode $element)
    {
        $innerHTML = "";
        $children  = $element->childNodes;

        foreach ($children as $child)
        {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }

    function shouldTranslateWholeTag($element){
        for($i = 0; $i < count($element->childNodes); $i++){
            $child = $element->childNodes->item($i);

            if(in_array(strtoupper($child->nodeName), $this->siblingsAvoidArray)){
                return false;
            }
        }
        return true;
    }

    function allowTranslateWholeTag($element){
        for($i = 0; $i < count($element->childNodes); $i++){
            $child = $element->childNodes->item($i);

            if(in_array(strtoupper($child->nodeName), $this->siblingsAllowArray)){
                $outerHTML = $element->ownerDocument->saveHTML($child);

                if(preg_match("/>(\s*[^<>\s]+[\s\S]*?)</", $outerHTML)){
                    return true;
                }else if(strtoupper($child->nodeName) == "BR"){
                    $innerHTML = $this->DOMinnerHTML($element);

                    if(preg_match("/\s*[^<>\s]+\s*<br>\s*[^<>\s]+/i", $innerHTML)){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function isTextNodeExists($element){
        for($i = 0; $i < count($element->childNodes); $i++){
            $child = $element->childNodes->item($i);

            if($child->nodeName == "#text" && trim($child->textContent)){
                return true;
            }
        }
        return false;
    }

    // DOM

    function domRecursiveRead( $doc )
    {
        foreach( $doc->childNodes as $child )
        {
            if( $child->nodeType === 3 )
            {
                $value = trim( $child->textContent );
                // $value = htmlentities($child->textContent, null, 'utf-8');
                // $value = str_ireplace("&nbsp;", " ", $value);
                // $value = trim($value);

                if( !empty( $value ) )
                {
                    if	($child->nextSibling || $child->previousSibling) {

                        if($child->parentNode && $this->allowTranslateWholeTag($child->parentNode) && $this->shouldTranslateWholeTag($child->parentNode)){
                            $value = trim($this->DOMinnerHTML($child->parentNode));
                            $value = preg_replace("/\<!--(.*?)\-->/", "", $value);
                            $this->segments[$value] = $value;
                        }else
                            $this->segments[$value] = $value;
                    }
                    else
                        $this->segments[$value] = $value;
                }

            }
            else
            {
                if( $child->nodeType === 1 )
                {
                    if( $child->hasAttribute('title') )
                    {
                        $attrValue = trim( $child->getAttribute('title') );
                    }

                    if( $child->hasAttribute('alt') )
                    {
                        $attrValue = trim( $child->getAttribute('alt') );
                    }

                    if( $child->hasAttribute('placeholder') )
                    {
                        $attrValue = trim( $child->getAttribute('placeholder') );
                    }

                    if( $child->hasAttribute( 'type' ) )
                    {
                        $attrTypeValue = trim( $child->getAttribute( 'type' ) );

                        if( strcasecmp( $attrTypeValue, 'submit' ) === 0 || strcasecmp( $attrTypeValue, 'reset' ) === 0)
                        {
                            if( $child->hasAttribute( 'value' ) )
                            {
                                $attrValue = trim( $child->getAttribute( 'value' ) );
                            }
                        }
                    }

                    if( !empty( $attrValue ) )
                    {
                        $this->segments[$attrValue] = $attrValue;
                    }

                    if( strcasecmp( $child->nodeName, 'meta' ) === 0 )
                    {
                        if( $child->hasAttribute('name') )
                        {
                            $metaAttributeName = trim( $child->getAttribute('name') );

                            if( strcasecmp( $metaAttributeName, 'description' ) === 0 || strcasecmp( $metaAttributeName, 'keywords' ) === 0 )
                            {
                                if( $child->hasAttribute('content') )
                                {
                                    $metaAttrValue = trim( $child->getAttribute('content') );

                                    if( !empty( $metaAttrValue ) )
                                    {
                                        $this->segments[$metaAttrValue] = $metaAttrValue;
                                    }
                                }
                            }
                        }
                    }

                    if($child->nodeName == 'img'){
                        // if(window.conveythis.translate_media){
                        // // console.log(el["src"]);
                        // apply( el, 'src' )( translate );
                        // }
                        if($this->translate_media){
                            $src = $child->getAttribute("src");
                            $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
                            if(strpos($ext,"?") !== false) $ext = substr($ext, 0, strpos($ext,"?"));

                            if(in_array($ext, $this->imageExt)){
                                // echo $src . "<br>";
                                $this->segments[$src] = $src;
                            }
                        }
                    }


                    $shouldReadChild = true;
                    if($child->nodeName == 'a'){

                        if($this->translate_document){
                            $href = $child->getAttribute("href");
                            $ext = strtolower(pathinfo($href, PATHINFO_EXTENSION));
                            if(strpos($ext,"?") !== false) $ext = substr($ext, 0, strpos($ext,"?"));

                            if(in_array($ext, $this->documentExt)){
                                // echo $href . "<br>";
                                $this->segments[$href] = $href;

                            }
                        }

                        if($this->translate_links) {
                            $href = $child->getAttribute("href");
                            $pageHost = $this->getPageHost($href);
                            $link = parse_url($href);
                            if ((!$pageHost || $pageHost == $this->site_host) && $link['path'] && $link['path'] != '/') {
                                $this->segments[$link['path']] = $link['path'];
                                $this->links[$link['path']] = $link['path'];
                            }
                        }

                        $translateAttr = $child->getAttribute("translate");
                        if($translateAttr && $translateAttr == "no"){
                            // no need to walk inside
                            $shouldReadChild = false;
                        }
                    }

                    if( in_array(strtoupper($child->nodeName), $this->siblingsAllowArray) ){

                        if($child->parentNode){
                            if($this->isTextNodeExists($child->parentNode) && $this->allowTranslateWholeTag($child->parentNode) && $this->shouldTranslateWholeTag($child->parentNode)){
                                // no need to walk inside
                                $shouldReadChild = false;
                            }
                        }
                    }

                    if ($child->hasAttribute('class')) {
                        $class = $child->getAttribute("class");
                        if (strpos($class, 'conveythis-no-translate') !== false) {
                            // no need to walk inside
                            $shouldReadChild = false;
                        }
                    }

                    foreach ($this->exclusion_block_ids as $exclusionBlockId) {
                        if ($child->hasAttribute('id') && $child->getAttribute("id") == $exclusionBlockId) {
                            // no need to walk inside
                            $shouldReadChild = false;
                            break;
                        }
                    }

                    if( strcasecmp( $child->nodeName, 'script' ) !== 0 && strcasecmp( $child->nodeName, 'style' ) !== 0 && $shouldReadChild == true )
                    {
                        $this->domRecursiveRead( $child );
                    }
                }
            }
        }
    }

    function replaceSegments( $output )
    {

        foreach($this->siblingsAvoidArray as $key=>$value){
            $output = preg_replace_callback( '#(<\s*?'.strtolower($value).'\b[^>]*>)(.*)(</'.strtolower($value).'\b[^>]*>)#s', function ( $matches ) use ($value) {

                $segment = $this->searchSegment( $matches[2] );

                if( !empty( $segment ) )
                {
                    return $matches[1] . $segment . $matches[3];
                }
                else
                {
                    // required to correctly find deepest element eg.: <div><div><div>text<a href="link"> link</a></div></div></div>
                    $tempOutput =  preg_replace_callback( '#<'.strtolower($value).'\b[^>]*>(?!<'.strtolower($value).'\b[^>]*>)(?:[\S\s](?!<'.strtolower($value).'\b[^>]*>))*?</'.strtolower($value).'>#', function ( $matches ) use ($value) {

                        if(isset($matches[0])){

                            return preg_replace_callback( '#(<\s*?'.strtolower($value).'\b[^>]*>)(.*?)(</'.strtolower($value).'\b[^>]*>)#s', function ( $matches ) {

                                if (isset($matches[2]) && preg_match("#(<.*>)#", $matches[2])) { // segment should not be plain text
                                    $segment = $this->searchSegment( $matches[2] );

                                    if( !empty( $segment ) )
                                    {
                                        return $matches[1] . $segment . $matches[3];
                                    }
                                    else
                                    {
                                        return $matches[0];
                                    }
                                }else{
                                    return $matches[0];
                                }
                            }, $matches[0]);
                        }else{
                            return null;
                        }

                    }, $matches[0]);

                    return $tempOutput ? $tempOutput : $matches[0];
                }

            }, $output);
        }
        foreach($this->siblingsAvoidArray as $key=>$value){
            $output = preg_replace_callback( '#(<\s*?'.strtolower($value).'\b[^>]*>)(.*)(</'.strtolower($value).'\b[^>]*>)#', function ( $matches ) use ($value) {

                $segment = $this->searchSegment( $matches[2] );

                if( !empty( $segment ) )
                {
                    return $matches[1] . $segment . $matches[3];
                }

                else
                {
                    // required to correctly find deepest element eg.: <div><div><div>text<a href="link"> link</a></div></div></div>
                    $tempOutput =  preg_replace_callback( '#<'.strtolower($value).'\b[^>]*>(?!<'.strtolower($value).'\b[^>]*>)(?:[\S\s](?!<'.strtolower($value).'\b[^>]*>))*?</'.strtolower($value).'>#', function ( $matches ) use ($value) {

                        if(isset($matches[0])){

                            return preg_replace_callback( '#(<\s*?'.strtolower($value).'\b[^>]*>)(.*?)(</'.strtolower($value).'\b[^>]*>)#s', function ( $matches ) {

                                if (isset($matches[2]) && preg_match("#(<.*>)#", $matches[2])) { // segment should not be plain text
                                    $segment = $this->searchSegment( $matches[2] );

                                    if( !empty( $segment ) )
                                    {
                                        return $matches[1] . $segment . $matches[3];
                                    }
                                    else
                                    {
                                        return $matches[0];
                                    }
                                }else{
                                    return $matches[0];
                                }

                            }, $matches[0]);
                        }else{

                            return null;
                        }

                    }, $matches[0]);

                    return $tempOutput ? $tempOutput : $matches[0];
                }

            }, $output);
        }

        foreach($this->siblingsAllowArray as $key=>$value){
            $output = preg_replace_callback( '#(<\s*?'.strtolower($value).'\b[^>]*>)(.*)(</'.strtolower($value).'\b[^>]*>)#s', function ( $matches ) use ($value) {

                if( !preg_match( '/translate="no"/', $matches[0] ) )
                {
                    $segment = $this->searchSegment( $matches[2] );

                    if( !empty( $segment ) )
                    {
                        return $matches[1] . $segment . $matches[3];
                    }

                    else
                    {
                        // required to correctly find deepest element eg.: <div><div><div>text<a href="link"> link</a></div></div></div>
                        $tempOutput =  preg_replace_callback( '#<'.strtolower($value).'\b[^>]*>(?!<'.strtolower($value).'\b[^>]*>)(?:[\S\s](?!<'.strtolower($value).'\b[^>]*>))*?</'.strtolower($value).'>#s', function ( $matches ) use ($value) {

                            if(isset($matches[0])){

                                return preg_replace_callback( '#(<\s*?'.strtolower($value).'\b[^>]*>)(.*?)(</'.strtolower($value).'\b[^>]*>)#s', function ( $matches ) {

                                    if (isset($matches[2]) && preg_match("#(<.*>)#", $matches[2])) { // segment should not be plain text
                                        $segment = $this->searchSegment( $matches[2] );

                                        if( !empty( $segment ) )
                                        {
                                            return $matches[1] . $segment . $matches[3];
                                        }
                                        else
                                        {
                                            return $matches[0];
                                        }
                                    }else{
                                        return $matches[0];
                                    }

                                }, $matches[0]);
                            }else{

                                return null;
                            }

                        }, $matches[0]);

                        return $tempOutput ? $tempOutput : $matches[0];
                    }
                }
                else
                {
                    return $matches[0];
                }

            }, $output);
        }
        foreach($this->siblingsAllowArray as $key=>$value){
            $output = preg_replace_callback( '#(<\s*?'.strtolower($value).'\b[^>]*>)(.*)(</'.strtolower($value).'\b[^>]*>)#', function ( $matches ) use ($value) {

                if( !preg_match( '/translate="no"/', $matches[0] ) )
                {
                    $segment = $this->searchSegment( $matches[2] );

                    if( !empty( $segment ) )
                    {
                        return $matches[1] . $segment . $matches[3];
                    }

                    else
                    {
                        // required to correctly find deepest element eg.: <div><div><div>text<a href="link"> link</a></div></div></div>
                        $tempOutput =  preg_replace_callback( '#<'.strtolower($value).'\b[^>]*>(?!<'.strtolower($value).'\b[^>]*>)(?:[\S\s](?!<'.strtolower($value).'\b[^>]*>))*?</'.strtolower($value).'>#s', function ( $matches ) use ($value) {

                            if(isset($matches[0])){

                                return preg_replace_callback( '#(<\s*?'.strtolower($value).'\b[^>]*>)(.*?)(</'.strtolower($value).'\b[^>]*>)#s', function ( $matches ) {

                                    if (isset($matches[2]) && preg_match("#(<.*>)#", $matches[2])) { // segment should not be plain text
                                        $segment = $this->searchSegment( $matches[2] );

                                        if( !empty( $segment ) )
                                        {
                                            return $matches[1] . $segment . $matches[3];
                                        }
                                        else
                                        {
                                            return $matches[0];
                                        }
                                    }else{
                                        return $matches[0];
                                    }

                                }, $matches[0]);
                            }else{

                                return null;
                            }

                        }, $matches[0]);

                        return $tempOutput ? $tempOutput : $matches[0];
                    }
                }
                else
                {
                    return $matches[0];
                }

            }, $output);
        }


        $output = preg_replace_callback( '/>([^<>]+)</', function ( $matches ) {

            $segment = $this->searchSegment( $matches[1] );

            if( !empty( $segment ) )
            {
                return '>' . $segment . '<';
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        $output = preg_replace_callback('/(content|placeholder|alt|title)(\s+)?=(\s+)?"([^"]+)"/', function ( $matches ) {

            $segment = $this->searchSegment( $matches[4] );

            if( !empty( $segment ) )
            {
                return $matches[1] . '="' . $segment . '"';
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        $output = preg_replace_callback("/(content|placeholder|alt|title)(\s+)?=(\s+)?'([^']+)'/", function ( $matches ) {

            $segment = $this->searchSegment( $matches[4] );

            if( !empty( $segment ) )
            {
                return $matches[1] . "='" . $segment . "'";
            }

            else
            {
                return $matches[0];
            }

        }, $output);


        $output = preg_replace_callback('/type="(submit|reset)"(\s+)?value=(\s+)?"([^"]+)"/', function ( $matches ) {

            $segment = $this->searchSegment( $matches[4] );

            if( !empty( $segment ) )
            {
                return 'type='.$matches[1].' value="'.esc_html($segment).'"';
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        $output = preg_replace_callback( '/(<(\s+)?a([^<]+)href(\s+)?=(\s+)?)"([^"]+)"(([^>]+)?>)/', function ( $matches ) {

            if( !preg_match( '/translate="no"/', $matches[0] ) && !preg_match( '/\/wp-content\//', $matches[0] ) )
            {
                $temp = $this->replaceLink( $matches[6], $this->language_code );

                if( !empty( $temp ) )
                {
                    return $matches[1] . '"' . $temp . '"' . $matches[7];
                }

                else
                {
                    return $matches[0];
                }
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        $output = preg_replace_callback( "/(<(\s+)?a([^<]+)href(\s+)?=(\s+)?)'([^']+)'(([^>]+)?>)/", function ( $matches ) {

            if( !preg_match( '/translate="no"/', $matches[0] ) && !preg_match( '/\/wp-content\//', $matches[0] ) )
            {
                $temp = $this->replaceLink( $matches[6], $this->language_code );

                if( !empty( $temp ) )
                {
                    return $matches[1] . "'" . $temp . "'" . $matches[7];
                }

                else
                {
                    return $matches[0];
                }
            }

            else
            {
                return $matches[0];
            }

        }, $output);


        $output = preg_replace_callback( '/(<(\s+)?img([^<]+)src(\s+)?=(\s+)?)"([^"]+)"/', function ( $matches ) {

            $metaAttrValue = $matches[6];

            if( strpos( $metaAttrValue, '//' ) === false )
            {

                if( strncmp( $metaAttrValue, $this->site_url, strlen( $this->site_url ) ) !== 0 )
                {
                    $newAttrValue = rtrim( $this->site_url, '/' ) . '/' . ltrim( $metaAttrValue, '/' );
                    return $matches[1] . '"' . $newAttrValue . '"';
                }
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        if($this->translate_media){
            $output = preg_replace_callback( '/(<(\s+)?img([^<]+)src(\s+)?=(\s+)?)"([^"]+)"/', function ( $matches ) {

                $metaAttrValue = $matches[6];

                if( !preg_match( '/translate="no"/', $matches[0] ) )
                {

                    $segment = $this->searchSegment( $matches[6] );

                    if( !empty( $segment ) )
                    {
                        return $matches[1] . '"' . $segment . '" srcset=""';
                    }

                    else
                    {
                        return $matches[0];
                    }
                }

                else
                {
                    return $matches[0];
                }

            }, $output);
        }

        if($this->translate_document){
            $output = preg_replace_callback( '/(<(\s+)?a([^<]+)href(\s+)?=(\s+)?)"([^"]+)"/', function ( $matches ) {

                $metaAttrValue = $matches[6];

                if( !preg_match( '/translate="no"/', $matches[0] ) )
                {
                    $segment = $this->searchSegment( $matches[6] );

                    if( !empty( $segment ) )
                    {
                        return $matches[1] . '"' . $segment . '"';
                    }

                    else
                    {
                        return $matches[0];
                    }
                }

                else
                {
                    return $matches[0];
                }

            }, $output);
        }


        $output = preg_replace_callback( '/(<(\s+)?link([^<]+)canonical([^<]+)href(\s+)?=(\s+)?)"([^"]+)"/', function ( $matches ) {

            $temp = $this->replaceLink( $matches[7], $this->language_code );

            if( !empty( $temp ) )
            {
                return $matches[1] . '"' . $temp . '"';
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        $output = preg_replace_callback( "/(<(\s+)?link([^<]+)canonical([^<]+)href(\s+)?=(\s+)?)'([^']+)'/", function ( $matches ) {

            $temp = $this->replaceLink( $matches[7], $this->language_code );

            if( !empty( $temp ) )
            {
                return $matches[1] . "'" . $temp . "'";
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        $output = preg_replace_callback( '/(<(\s+)?article([^<]+)data-permalink(\s+)?=(\s+)?)"([^"]+)"(([^>]+)?>)/', function ( $matches ) {

            if( !preg_match( '/translate="no"/', $matches[0] ) )
            {
                $temp = $this->replaceLink( $matches[6], $this->language_code );

                if( !empty( $temp ) )
                {
                    return $matches[1] . '"' . $temp . '"' . $matches[7];
                }

                else
                {
                    return $matches[0];
                }
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        $output = preg_replace_callback( '/(<(\s+)?form([^<]+)action(\s+)?=(\s+)?)"([^"]+)"/', function ( $matches ) {

            if( !preg_match( '/translate="no"/', $matches[0] ) )
            {
                $temp = $this->replaceLink( $matches[6], $this->language_code );

                if( !empty( $temp ) )
                {
                    return $matches[1] . '"' . $temp . '"';
                }

                else
                {
                    return $matches[0];
                }
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        $output = preg_replace_callback( "/(<(\s+)?form([^<]+)action(\s+)?=(\s+)?)'([^']+)'/", function ( $matches ) {

            if( !preg_match( '/translate="no"/', $matches[0] ) )
            {
                $temp = $this->replaceLink( $matches[6], $this->language_code );

                if( !empty( $temp ) )
                {
                    return $matches[1] . '"' . $temp . '"';
                }

                else
                {
                    return $matches[0];
                }
            }

            else
            {
                return $matches[0];
            }

        }, $output);

        return $output;

    }

    function domRecursiveApply( $doc, $items )
    {
        foreach( $doc->childNodes as $child )
        {
            if( $child->nodeType === 3 )
            {
                $value = $child->textContent;
                $segment = $this->searchSegment( $value, $items );

                if( !empty( $segment ) )
                {
                    $child->textContent = $segment;
                }
            }

            else
            {
                if( $child->nodeType === 1 )
                {
                    if( $child->hasAttribute( 'title' ) )
                    {
                        $attrValue = $child->getAttribute( 'title' );
                        $segment = $this->searchSegment( $attrValue, $items );

                        if( !empty( $segment ) )
                        {
                            $child->setAttribute( 'title', $segment );
                        }
                    }

                    if( $child->hasAttribute( 'alt' ) )
                    {
                        $attrValue = $child->getAttribute( 'alt' );
                        $segment = $this->searchSegment( $attrValue, $items );

                        if( !empty( $segment ) )
                        {
                            $child->setAttribute( 'alt', $segment );
                        }
                    }

                    if( $child->hasAttribute( 'placeholder' ) )
                    {
                        $attrValue = $child->getAttribute( 'placeholder' );
                        $segment = $this->searchSegment( $attrValue, $items );

                        if( !empty( $segment ) )
                        {
                            $child->setAttribute( 'placeholder', $segment );
                        }
                    }

                    if( $child->hasAttribute( 'type' ) )
                    {
                        $attrValue = trim( $child->getAttribute( 'type' ) );

                        if( strcasecmp( $attrValue, 'submit' ) === 0 || strcasecmp( $attrValue, 'reset' ) === 0 )
                        {
                            if( $child->hasAttribute( 'value' ) )
                            {
                                $attrValue = $child->getAttribute( 'value' );
                                $segment = $this->searchSegment( $attrValue, $items );

                                if( !empty( $segment ) )
                                {
                                    $child->setAttribute( 'value', $segment );
                                }
                            }
                        }
                    }

                    if( strcasecmp( $child->nodeName, 'img' ) === 0 )
                    {
                        if( $child->hasAttribute( 'src' ) )
                        {
                            $metaAttrValue = trim( $child->getAttribute( 'src' ) );

                            if( !empty( $metaAttrValue ) )
                            {
                                if( strpos( $metaAttrValue, '//' ) === false )
                                {
                                    if( strncmp( $metaAttrValue, $this->site_url, strlen( $this->site_url ) ) !== 0 )
                                    {
                                        $newAttrValue = rtrim( $this->site_url, '/' ) . '/' . ltrim( $metaAttrValue, '/' );

                                        $child->setAttribute( 'src', $newAttrValue );
                                    }
                                }
                            }
                        }
                    }

                    if( strcasecmp( $child->nodeName, 'a' ) === 0 )
                    {
                        if( $child->hasAttribute( 'href' ) )
                        {
                            $metaAttrValue = trim( $child->getAttribute( 'href' ) );

                            if( !empty( $metaAttrValue ) )
                            {
                                if( $metaAttrValue !== '#' )
                                {
                                    if( $child->hasAttribute( 'translate' ) )
                                    {
                                        $metaAttrValue = trim( $child->getAttribute( 'translate' ) );

                                        if( $metaAttrValue === 'no' )
                                        {

                                        }

                                        else
                                        {
                                            $temp = $this->replaceLink( $metaAttrValue, $this->language_code );
                                            $child->setAttribute( 'href', $temp );
                                        }
                                    }

                                    else
                                    {
                                        $temp = $this->replaceLink( $metaAttrValue, $this->language_code );
                                        $child->setAttribute( 'href', $temp );
                                    }
                                }
                            }
                        }
                    }

                    if( strcasecmp( $child->nodeName, 'meta' ) === 0 )
                    {
                        if( $child->hasAttribute( 'name' ) )
                        {
                            $metaAttributeName = trim( $child->getAttribute( 'name' ) );

                            if( strcasecmp( $metaAttributeName, 'description' ) === 0 || strcasecmp( $metaAttributeName, 'keywords' ) === 0 )
                            {
                                if( $child->hasAttribute( 'content' ) )
                                {
                                    $metaAttrValue = $child->getAttribute( 'content' );
                                    $segment = $this->searchSegment( $metaAttrValue, $items );

                                    if( !empty( $segment ) )
                                    {
                                        $child->setAttribute( 'content', $segment );
                                    }
                                }
                            }
                        }
                    }

                    if( strcasecmp( $child->nodeName, 'script' ) !== 0 && strcasecmp( $child->nodeName, 'style' ) !== 0 )
                    {
                        if( $child->hasAttribute( 'translate' ) )
                        {
                            $metaAttrValue = trim( $child->getAttribute( 'translate' ) );

                            if( $metaAttrValue === 'no' )
                            {

                            }

                            else
                            {
                                $this->domRecursiveApply( $child, $items );
                            }
                        }

                        else
                        {
                            $this->domRecursiveApply( $child, $items );
                        }
                    }
                }
            }
        }
    }

    function replaceLink( $value, $language_code )
    {
        $aPos = strpos( $value, '//' );

        if( $aPos !== false )
        {
            $ePos = strpos( $this->site_url, '//' );
            $aStr = substr( $value, $aPos );
            $eStr = substr( $this->site_url, $ePos );
            $eLen = strlen( $eStr );

            if( strncmp( $aStr, $eStr, $eLen ) !== 0 )
            {
                return $value;
            }
        }

        if ( strpos( $value, '#' ) === 0 || strpos( $value, 'mailto:' ) === 0 || strpos( $value, 'tel:' ) === 0){
            return $value;
        }

        $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
        if(strpos($ext,"?") !== false) $ext = substr($ext, 0, strpos($ext,"?"));

        if(in_array($ext, $this->avoidUrlExt)){
            return $value;
        }
        //

        if (isset($this->target_languages_translations[$language_code])) {
            $language_code = $this->target_languages_translations[$language_code];
        }
        $link = parse_url( $value );

        if( isset($link['path']) && stripos( $link['path'], 'wp-admin' ) === false )
        {
            if( $this->translate_links ) {
                $pageHost = $this->getPageHost( $value );
                if ( ( !$pageHost || $pageHost == $this->site_host ) && $link['path'] && $link['path'] != '/' ) {
                    $segment = $this->searchSegment( $link['path'] );
                    if ( $segment ) {
                        $link['path'] = $segment;
                    }
                }
            }
            $link['path'] = substr_replace( $link['path'], $this->site_prefix . '' . $language_code . '/', 0, strlen( $this->site_prefix ) );

            return $this->unparse_url( $link );
        }

        return $value;
    }

    function unparse_url( $parsed_url )
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    function domLoad( $output )
    {
        $doc = new DOMDocument();

        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        libxml_use_internal_errors( true );

        if (extension_loaded('mbstring')) {
            $doc->loadHTML(mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8'));
        }else{
            $doc->loadHTML( $output );
        }

        libxml_clear_errors();

        return $doc;
    }

    function searchSegment( $value )
    {
        $source_text = html_entity_decode( $value );
        // $source_text = str_ireplace("&nbsp;", " ", $value);
        // $source_text = html_entity_decode(trim($source_text));
        $source_text = trim( preg_replace("/\<!--(.*?)\-->/", "", $source_text) );
        if (count( $this->segments_hash ) && !isset( $this->segments_hash[md5( $source_text ) ] ) )
        {
            return false;
        }
        if( !empty( $this->items ) && !empty( $source_text ) )
        {
            foreach( $this->items as $item )
            {
                $source_text2 = isset( $item['source_text']  ) ? html_entity_decode( $item['source_text'] ) : '';
                if( strcmp( $source_text, trim( $source_text2 ) ) === 0 )
                {
                    return str_replace( $source_text, $item['translate_text'], $source_text );
                }
            }
            $source_text = trim ( mb_strtolower( $source_text, 'UTF-8') );
            foreach( $this->items as $item )
            {
                $source_text2 = isset( $item['source_text']  ) ? html_entity_decode( $item['source_text'] ) : '';
                if( strcmp( $source_text, trim( mb_strtolower( $source_text2, 'UTF-8') ) ) === 0 )
                {
                    return str_replace( $source_text, $item['translate_text'], $source_text );
                }
            }
        }
    }

    function _translatePage($content){
        if( !is_admin() && !empty($this->language_code) && !empty($content) )
        {
            if( extension_loaded('xml') )
            {
                $scriptContainer = [];
                /* strip all JS content */
                $content = preg_replace_callback ("#<script([^>]*)>(.*?)</script>#s", function ($matches) use (&$scriptContainer) {
                    $scriptContainer[md5($matches[2])] = $matches[2];
                    return "<script".$matches[1].">".md5($matches[2])."</script>";
                }, $content);
                /* ----- */
                $doc = $this->domLoad( $content );

                $language = $this->searchLanguage( $this->language_code );
                if ( isset( $language['rtl'] ) && $this->change_direction && $doc->documentElement )
                {
                    $doc->documentElement->setAttribute('dir', 'rtl' );
                }

                $content = $doc->saveHTML();


                if($this->plan != 'free'){

                    $this->domRecursiveRead( $doc );
                    sort( $this->segments );

                    $update_cache = isset( $_POST['action'] ) && $_POST['action'] == 'conveythis_update_cache' ? true : false;

                    $cacheKey = md5(serialize(array_merge($this->segments,$this->links,[$this->referrer])));
                    $this->items = $this->get_cached_translations($this->source_language, $this->language_code, $this->referrer, $cacheKey);

                    if (empty($this->items)) {
                        for ($i = 1; $i <= 3; $i++) {
                            $response = $this->send('POST', '/website/translate/', [
                                'referrer' => $this->referrer,
                                'source_language' => $this->source_language,
                                'target_language' => $this->language_code,
                                'segments' => $this->segments,
                                'links' => $this->links
                            ], true);

                            if (isset($response['error'])) {
                                if (!$update_cache) {
                                    header('Location: ' . $this->referrer, true, 302);
                                    exit();
                                }
                                break;
                            }

                            if (!empty($response)) {
                                $this->items = $response;
                                break;
                            }
                        }

                        $this->save_cached_translations(
                            $this->source_language,
                            $this->language_code,
                            $this->referrer,
                            $this->items,
                            $cacheKey
                        );

                        $clearUrl = $this->getTranslateSiteUrl($this->referrer, $this->language_code);
                        $this->clearPageCache($clearUrl);

                        if ( $update_cache ) {
                            return json_encode( array('success' => true ) );
                        }
                    }
                }

                foreach ($this->segments as $segment) {
                    $source_text = trim(preg_replace("/\<!--(.*?)\-->/", "", html_entity_decode($segment)));
                    $this->segments_hash[md5($source_text)] = 1;
                }
                $content = $this->replaceSegments( $content );
                // return JS content
                $content = strtr($content, $scriptContainer);
            }
        }
        return $content;
    }

    function customLogs($message) {
        $uploads  = wp_upload_dir( null, false );
        $logs_dir = $uploads['basedir'] . '/conveythis';
        if ( ! is_dir( $logs_dir ) ) {
            mkdir( $logs_dir, 0755, true );
        }
        $file = fopen( $logs_dir . '/' . 'log.log', 'a' );
        fwrite($file, date('Y-m-d h:i:s') . " :: " . print_r($message, true) . PHP_EOL);
        fclose($file);
    }

    private function updateRules($rules, $type) {
        if (is_string($rules)) {
            $rules = json_decode($rules, true);
        }
        if ($type == 'exclusion')
        {
            $this->send('POST', '/admin/account/domain/pages/excluded/', array(
                'referrer' => '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'rules' => $rules
            ));
        }elseif ($type == 'glossary')
        {
            $this->send('POST', '/admin/account/domain/pages/glossary/', array(
                'referrer' => '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'rules' => $rules
            ));
        }elseif ($type == 'exclusion_blocks')
        {
            $this->send('POST', '/admin/account/domain/excluded/blocks/', array(
                'referrer' => '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'blocks' => $rules
            ));
        }
    }

    private function send($request_method = 'GET', $request_uri = '', $query = [], $return_error = false)
    {
        $headers = [
            'X-Api-Key' => $this->api_key
        ];
        if (count($query)) {
            $headers['Content-Type'] = 'application/json; charset=UTF-8';
        }
        if (strpos($request_uri, '/admin/') === 0) {
            $headers['X-Auth-Token'] = API_AUTH_TOKEN;
        }

        $args = [
            'headers' => $headers,
            'body' => count($query) ? json_encode($query) : null,
            'method' => $request_method,
            'redirection' => '10',
            'httpversion' => '1.1',
            'blocking' => true,
            'cookies' => []
        ];

        $response = $this->httpRequest( $request_uri, $args );

        if(!is_array($response)) {
            return [];
        }

        $body = $response['body'];
        $code = $response['response']['code'];

        if( !empty( $body ) )
        {
            $data = json_decode( $body, true );

            if( !empty( $data ) )
            {
                if( $data['status'] == 'success' )
                {
                    return $data['data'];
                }
                else if($data['status'] == 'error'){
                    if ( $return_error )
                    {
                        return ['error' => $data['message']];
                    }
                    return [];
                }
                else
                {
                    if( !empty( $data['message'] ) )
                    {

                        if( is_admin() )
                        {

                            if (!function_exists('add_settings_error')) {
                                include_once(ABSPATH . 'wp-admin/includes/template.php');
                            }

                            $message = esc_html__( $data['message'], 'conveythis-translate' );

                            if( strpos( $message, '#' ) )
                            {
                                $message = str_replace( '#', '<a target="_blank" href="https://www.conveythis.com/dashboard/pricing/?utm_source=widget&utm_medium=wordpress">' . __( 'change plan', 'conveythis-translate' ) . '</a>', $message );
                            }

                            add_settings_error( 'conveythis-translate', '501', $message, 'error' );
                        }
                    }
                }
            }
        }
        return null;
    }

    private static function httpRequest( $url, $args = [] ) {
        $args['timeout'] = 2;
        $response = wp_remote_request( CONVEYTHIS_API_PROXY_URL. $url, $args );
        if( is_wp_error( $response ) ) {
            $args['timeout'] = 30;
            $response = wp_remote_request( CONVEYTHIS_API_URL. $url, $args );
        }
        return $response;
    }

    private function find_translation($slug, $source_language, $target_language, $referer) {
        $response = $this->send('POST', '/website/find-translation/', array(
            'referrer' => $referer,
            'source_language' => $source_language,
            'target_language' => $target_language,
            'segments' => [$slug]
        ));
        if (count($response)) {
            return $response[0]['translate_text'];
        }
        return false;
    }

    private function find_original_slug($slug, $source_language, $target_language, $referer) {
        $original_slug = $this->get_cached_slug($slug, $target_language, $source_language);
        if (!$original_slug) {
            $response = $this->send('POST', '/website/find-translation-source/', array(
                'referrer' => $referer,
                'source_language' => $source_language,
                'target_language' => $target_language,
                'segments' => [$slug]
            ));

            if (count($response)) {
                $original_slug = $response[0]['source_text'];
                if ( $original_slug ) {
                    $this->save_cached_slug($slug, $target_language, $source_language, $original_slug);
                }
            }
        }
        return $original_slug;
    }

    private function get_cached_slug( $slug, $source_language, $target_language ) {
        if (file_exists( CONVEYTHIS_CACHE_SLUG_PATH )) {
            $slug_list = json_decode( file_get_contents(CONVEYTHIS_CACHE_SLUG_PATH), true );
            if ( !empty($slug_list) ) {
                if ( isset($slug_list[$source_language][$slug][$target_language]) ) {
                    return $slug_list[$source_language][$slug][$target_language];
                }
            }
        }
        return false;
    }

    private function save_cached_slug( $slug, $source_language, $target_language, $value ) {
        if ( !file_exists( CONVEYTHIS_CACHE_PATH ) ) {
            mkdir( CONVEYTHIS_CACHE_PATH, 0777, true );
            $slug_list = array();
        }else {
            $slug_list = file_exists( CONVEYTHIS_CACHE_SLUG_PATH ) ? json_decode( file_get_contents( CONVEYTHIS_CACHE_SLUG_PATH ), true ) : [];
            if ( empty($slug_list) ) {
                $slug_list = array();
            }
        }
        if ( !isset($slug_list[$source_language]) ) {
            $slug_list[$source_language] = array();
        }
        if ( !isset($slug_list[$source_language][$slug]) ) {
            $slug_list[$source_language][$slug] = array();
        }
        $slug_list[$source_language][$slug][$target_language] = $value;
        file_put_contents(CONVEYTHIS_CACHE_SLUG_PATH, json_encode($slug_list) );
    }

    private function getTranslateSiteUrl($path, $targetLanguage = '')
    {
        $translateUrl = '';
        if (strlen($path) > 0 && strlen($targetLanguage) > 0) {
            $pageUrl = trim($path);
            $pageUrl = str_replace($this->site_url, '', $pageUrl);
            $pageUrl = str_replace($this->site_prefix, '', $pageUrl);
            $pageUrl = str_replace($this->site_host, '', $pageUrl);
            $pageUrl = str_replace('//', '/', $pageUrl);
            $translateUrl = $this->site_url . '/' . $targetLanguage . $pageUrl;
        }
        return $translateUrl;
    }

    private function get_cached_translations( $source_language, $target_language, $path, $cacheKey ) {
        $file = CONVEYTHIS_CACHE_TRANSLATIONS_PATH. $source_language. '_'. $target_language. '/'. md5($path). '.json';
        $cacheContent = [];
        if (file_exists($file)) {
            // If cache has been created/modified more than 3 days ago, delete it
            if (time() - filemtime($file) > 259200) {
                @unlink($file);
                return $cacheContent;
            }
            $fileContents = json_decode(file_get_contents($file), true);
            if ($fileContents[$cacheKey]) {
                $cacheContent = $fileContents[$cacheKey];
            }
        }
        return $cacheContent;
    }

    private function save_cached_translations($sourceLanguage, $targetLanguage, $path, $data, $cacheKey = 'cache_key')
    {
        $langDir = $this->getCacheLangDir($sourceLanguage, $targetLanguage);
        $cachePath = $langDir . $this->getCacheFileName($path);
        $cacheData[$cacheKey] = $data;
        if ($data) {
            if (!file_exists($langDir)) {
                mkdir($langDir, 0777, true);
            }
            file_put_contents($cachePath, json_encode($cacheData));
        } elseif (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

    private function clear_cached_translations($all = false, $path = '', $sourceLanguage = '', $targetLanguage = '')
    {
        $result = false;
        if ($all) {
            if (file_exists(CONVEYTHIS_CACHE_TRANSLATIONS_PATH) && is_dir(CONVEYTHIS_CACHE_TRANSLATIONS_PATH)) {
                $result = $this->clearDir(CONVEYTHIS_CACHE_TRANSLATIONS_PATH);
            }
        } else {
            if (strlen($path) > 0) {
                $cachePath = $this->getCacheLangDir($sourceLanguage, $targetLanguage) . $this->getCacheFileName($path);
                $result = unlink($cachePath);
            }
        }
        return $result;
    }

    public function getCacheSize()
    {
        if ($this->cacheTranslateSize == 0) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(CONVEYTHIS_CACHE_TRANSLATIONS_PATH, FilesystemIterator::SKIP_DOTS)
            );
            $size = 0;
            foreach ($iterator as $file) {
                $size += $file->getSize();
            }
            if ($size > 0) {
                $size = (round($size / 1048576 * 100) / 100);//MB
            }
            $this->cacheTranslateSize = $size;
        }
        return $this->cacheTranslateSize;
    }

    private function getCacheLangDir($sourceLanguage = '', $targetLanguage = '')
    {
        return CONVEYTHIS_CACHE_TRANSLATIONS_PATH . $sourceLanguage . '_' . $targetLanguage;
    }

    private function getCacheFileName($path)
    {
        return '/' . md5($path) . '.json';
    }

    private function clearDir($dir = '')
    {
        $clearResult = false;
        if (strlen($dir > 0)) {
            $dir_iterator = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
            foreach (new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::CHILD_FIRST) as $name => $item) {
                if (is_dir($name)) {
                    rmdir($name);
                } else {
                    unlink($name);
                }
            }
            $clearResult = rmdir($dir);
        }
        return $clearResult;
    }

    public function get_conveythis_shortcode(){

        $this->shortcode_counter++;
        return '<div id="conveythis_widget_placeholder_'.$this->shortcode_counter.'" class="conveythis_widget_placeholder"></div>';
    }


    public static function Instance()
    {
        if( self::$instance === null )
        {
            self::$instance = new ConveyThis();
        }
        return self::$instance;
    }

    public static function getCurrentDomain()
    {
        return str_ireplace('www.', '', parse_url(get_site_url(), PHP_URL_HOST));
    }

    public static function apiGetPluginSettings(){
        $domain = str_ireplace('www.', '', parse_url(get_site_url(), PHP_URL_HOST));
        $response = self::httpRequest( '/plugin/settings?domain='.$domain.'');
        if ( is_array( $response ) && ! is_wp_error( $response ) ) {
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );
            if (isset($data->data->api_key) && !empty($data->data->api_key)) {
                return [
                    'api_key' => 'pub_'.$data->data->api_key,
                ];
            }
        }
        return [
            'api_key' => '',
        ];
    }

    public static function plugin_activate()
    {
        add_option( 'api_key', '' );
        add_option( 'source_language', 'en' );
        add_option( 'target_languages', [] );
        add_option( 'target_languages_translations', [] );
        add_option( 'style_change_language', [] );
        add_option( 'style_change_flag', [] );
        add_option( 'style_flag', 'rect' );
        add_option( 'style_text', 'full-text' );
        add_option( 'style_position_vertical', 'bottom' );
        add_option( 'style_position_horizontal', 'left' );
        add_option( 'style_indenting_vertical', '12' );
        add_option( 'style_indenting_horizontal', '24' );
        add_option( 'auto_translate', '0' );
        add_option( 'hide_conveythis_logo', '0' );
        add_option( 'translate_media', '0' );
        add_option( 'translate_document', '0' );
        add_option( 'translate_links', '0' );
        add_option( 'no_translate_element_id', '' );
        add_option( 'change_direction', '0' );
        add_option( 'alternate', '1' );
        add_option( 'accept_language', '0' );
        add_option( 'blockpages', [] );
        add_option( 'show_javascript', '1' );
        add_option( 'mb_admin_notice', [] );
        add_option( 'style_position_type', 'fixed' );
        add_option( 'style_position_vertical_custom', 'bottom' );
        add_option( 'style_selector_id', '' );

        add_option( 'url_structure', 'regular' );

        add_option( 'style_background_color', '#ffffff' );
        add_option( 'style_hover_color', '#f6f6f6' );
        add_option( 'style_border_color', '#e0e0e0' );
        add_option( 'style_text_color', '#000000' );
        add_option( 'style_corner_type', 'cir' );
        add_option( 'style_widget', 'dropdown' );

        self::sendEvent('activate');
    }

    public static function plugin_deactivate()
    {
        self::sendEvent('deactivate');
    }

    public static function plugin_uninstall()
    {
        delete_option( 'api_key' );
        delete_option( 'source_language' );
        delete_option( 'target_languages' );
        delete_option( 'target_languages_translations' );
        delete_option( 'style_change_language' );
        delete_option( 'style_change_flag' );
        delete_option( 'style_flag' );
        delete_option( 'style_text' );
        delete_option( 'style_position_vertical' );
        delete_option( 'style_position_horizontal' );
        delete_option( 'style_indenting_vertical' );
        delete_option( 'style_indenting_horizontal' );
        delete_option( 'auto_translate' );
        delete_option( 'hide_conveythis_logo' );
        delete_option( 'translate_media' );
        delete_option( 'translate_document' );
        delete_option( 'translate_links' );
        delete_option( 'no_translate_element_id' );
        delete_option( 'change_direction' );
        delete_option( 'alternate' );
        delete_option( 'accept_language' );
        delete_option( 'blockpages' );
        delete_option( 'show_javascript' );
        delete_option( 'mb_admin_notice' );
        delete_option( 'style_position_type');
        delete_option( 'style_position_vertical_custom');
        delete_option( 'style_selector_id');
        delete_option( 'url_structure');

        delete_option( 'style_background_color');
        delete_option( 'style_hover_color');
        delete_option( 'style_border_color');
        delete_option( 'style_text_color');
        delete_option( 'style_corner_type');
        delete_option( 'style_widget');

        self::sendEvent('uninstall');
    }

    static function plugin_update_option($optionName, $oldValue, $newValue)
    {
        $pluginOption = false;
        $eventName = 'updOption';
        if(!empty($optionName)){
            if($optionName == 'api_key'){
                $pluginOption = true;
                if(empty($oldValue) && !empty($newValue)){
                    $eventName .= 'First';
                }
                if(!empty($oldValue) && !empty($newValue)){
                    $eventName .= 'Update';
                }
                $eventName .= 'ApiKey';
            }
        }

        if($pluginOption){
            self::sendEvent($eventName);
        }
    }

    static function redirect_after_activate($plugin)
    {
        if( $plugin == plugin_basename( __FILE__ ) ) {
            // Don't forget to exit() because wp_redirect doesn't exit automatically
            exit(wp_redirect(admin_url('options-general.php?page=convey_this')));
        }
    }

    public static function sendEvent($event = 'default', $message = '')
    {
        $key = get_option('api_key') ? get_option('api_key') : 'no_key';
        $response = self::httpRequest('/25/background/event/' . $key . '/' . base64_encode(self::getCurrentDomain()) . '/' . $event . '/');
    }

    function dismissNotice($function)
    {
        $metaName = 'convey_meta';
        $userMeta = get_user_meta(get_current_user_id(), $metaName, true);
        $userMeta = array_unique(array_filter(array_merge((array)$userMeta, [$function])));
        update_user_meta(get_current_user_id(), $metaName, $userMeta);
        delete_transient($function);
    }

    public function isDismiss($function)
    {
        $isDismiss = false;
        if (!empty($function)) {
            $userMeta = get_user_meta(get_current_user_id(), 'convey_meta', true);
            if (in_array($function, (array)$userMeta, true)) {
                $isDismiss = true;
            }
        }
        return $isDismiss;
    }

    public function getWidgetStyles(){
        return $this->widgetStyles;
    }
}

register_activation_hook( __FILE__, array( 'ConveyThis', 'plugin_activate' ) );
register_deactivation_hook( __FILE__, array( 'ConveyThis', 'plugin_deactivate' ) );
register_uninstall_hook( __FILE__, array( 'ConveyThis', 'plugin_uninstall' ) );

add_action( 'plugins_loaded', array( 'ConveyThis', 'Instance' ), 10 );
//add_action('activated_plugin', array('ConveyThis', 'flush_cache_on_activate'));
add_action('activated_plugin', array('ConveyThis', 'redirect_after_activate'));





class conveythis_admin_notices
{
    public $config;
    public $notice_spam = 0;
    public $notice_spam_max = 2;

    public function __construct( $config = array() )
    {
        add_action( 'admin_init', array( $this, 'mb_admin_notice_ignore' ) );
        add_action( 'admin_init', array( $this, 'mb_admin_notice_temp_ignore' ) );
        add_action('admin_notices', [$this, 'mb_display_admin_notices']);
    }

    public function mb_admin_notices()
    {
        $settings = get_option('mb_admin_notice');

        if( !isset($settings['disable_admin_notices']) || ( isset($settings['disable_admin_notices']) && $settings['disable_admin_notices'] == 0 ))
        {
            if( current_user_can('manage_options') )
            {
                return true;
            }
        }
        return false;
    }

    public function change_admin_notice_conveythis($admin_notices)
    {
        if (!$this->mb_admin_notices()) {
            return false;
        }

        foreach ($admin_notices as $slug => $admin_notice) {

            if ($this->mb_anti_notice_spam()) {
                return false;
            }

            if (isset($admin_notices[$slug]['pages']) && is_array($admin_notices[$slug]['pages'])) {
                if (!$this->mb_admin_notice_pages($admin_notices[$slug]['pages'])) {
                    return false;
                }
            }

            if (!$this->mb_required_fields($admin_notices[$slug]))
            {
                $current_date = current_time("m/d/Y");
                $start = date("m/d/Y");
                $date_array = explode('/', $start);
                $interval = ( isset($admin_notices[$slug]['int']) ? $admin_notices[$slug]['int'] : 0 );
                $date_array[1] += $interval;
                $start = date("m/d/Y", mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]));

                $admin_notices_option = get_option('mb_admin_notice', []);

                if (!array_key_exists($slug, $admin_notices_option)) {
                    $admin_notices_option[$slug]['start'] = $start;
                    $admin_notices_option[$slug]['int'] = $interval;
                    update_option('mb_admin_notice', $admin_notices_option);
                }

                $admin_display_check = ( isset($admin_notices_option[$slug]['dismissed']) ? $admin_notices_option[$slug]['dismissed'] : false );
                $admin_display_start = ( isset($admin_notices_option[$slug]['start']) ? $admin_notices_option[$slug]['start'] : $start );
                $admin_display_msg = ( isset($admin_notices[$slug]['msg']) ? $admin_notices[$slug]['msg'] : '' );
                $admin_display_link = ( isset($admin_notices[$slug]['link']) ? $admin_notices[$slug]['link'] : '' );

                if ($admin_display_check == 0 && strtotime($admin_display_start) <= strtotime($current_date)) {
                    if (strpos($slug, 'promo') === false) {
                        echo '<div class="update-nag mb-admin-notice notice notice-info is-dismissible" style="width:95%!important; display: flex;">
								<div style="max-width: 60px; margin-right: 20px;">
									<p><img src="'.plugins_url("images/ceo.jpg",__FILE__).'" style="width:100%; border-radius: 50%;"></p>
								</div>
								<div>
									<strong><p style="font-size:14px !important">' . $admin_display_msg . '</p></strong>
									<strong><ul style="line-height: 2rem;">' . $admin_display_link . '</ul></strong>
								</div>

							</div>';
                    } else {
                        echo '<div class="admin-notice-promo">';
                        echo $admin_display_msg;
                        echo '<ul class="notice-body-promo blue">
									' . $admin_display_link . '
								</ul>';
                        echo '</div>';
                    }
                    $this->notice_spam += 1;
                }
            }
        }
    }

    public function mb_anti_notice_spam()
    {
        if ($this->notice_spam >= $this->notice_spam_max) {
            return true;
        }
        return false;
    }

    public function mb_admin_notice_ignore()
    {
        if (isset($_GET['mb_admin_notice_ignore'])) {
            $admin_notices_option = get_option('mb_admin_notice', array());
            $admin_notices_option[$_GET['mb_admin_notice_ignore']]['dismissed'] = 1;
            update_option('mb_admin_notice', $admin_notices_option);
            $query_str = remove_query_arg('mb_admin_notice_ignore');
            wp_redirect($query_str);
            exit;
        }
    }

    public function mb_admin_notice_temp_ignore()
    {

        if (isset($_GET['mb_admin_notice_temp_ignore'])) {
            $admin_notices_option = get_option('mb_admin_notice', array());
            $current_date = current_time("m/d/Y");
            $date_array = explode('/', $current_date);
            $interval = (isset($_GET['mb_int']) ? filter_var($_GET['mb_int'], FILTER_SANITIZE_NUMBER_INT) : 7);

            $date_array[1] += $interval;
            $new_start = date("m/d/Y", mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]));

            $admin_notices_option[$_GET['mb_admin_notice_temp_ignore']]['start'] = $new_start;
            $admin_notices_option[$_GET['mb_admin_notice_temp_ignore']]['dismissed'] = 0;
            update_option('mb_admin_notice', $admin_notices_option);
            $query_str = remove_query_arg(array('mb_admin_notice_temp_ignore', 'mb_int'));
            wp_redirect($query_str);
            exit;
        }
    }

    public function mb_admin_notice_pages( $pages )
    {
        foreach ($pages as $key => $page) {
            if (is_array($page)) {
                if (isset($_GET['page']) && $_GET['page'] == $page[0] && isset($_GET['tab']) && $_GET['tab'] == $page[1]) {
                    return true;
                }
            } else {
                if ($page == 'all') {
                    return true;
                }
                if (get_current_screen()->id === $page) {
                    return true;
                }
                if (isset($_GET['page']) && $_GET['page'] == $page) {
                    return true;
                }
            }
            return false;
        }
    }

    public function mb_required_fields( $fields )
    {
        if (!isset($fields['msg']) || ( isset($fields['msg']) && empty($fields['msg']) )) {
            return true;
        }
        if (!isset($fields['title']) || ( isset($fields['title']) && empty($fields['title']) )) {
            return true;
        }
        return false;
    }

    public function mb_display_admin_notices()
    {
        $two_week_review_ignore = add_query_arg(['mb_admin_notice_ignore' => 'conveythis_two_week_review']);
        $two_week_review_temp = add_query_arg(['mb_admin_notice_temp_ignore' => 'conveythis_two_week_review', 'int' => 7]);

        $notices['conveythis_two_week_review'] = array(
            'title' => 'Leave A ConveyThis Review?',
            'msg' => 'Hello,<br><br>my name is Alex Buran. I\'m the founder of ConveyThis Translate plugin.<br>If you like this plugin, please write a few words about it at wordpress.org or twitter. Your opinion will help other people.<br><br>Thank you!',
            'link' => '<span class="conveythis-admin-notice"><a href="https://wordpress.org/support/plugin/conveythis-translate/reviews/?filter=5#postform" target="_blank" class="button button-primary conveythis-admin-notice-link">' . 'Rate plugin' . '</a></span>
					<span class="conveythis-admin-notice" style="margin-left: 20px;"><a href="' . $two_week_review_temp . '" class="conveythis-admin-notice-link">' . 'Remind me later' . '</a></span>
					<span class="conveythis-admin-notice" style="margin-left: 20px;"><a href="' . $two_week_review_ignore . '" class="conveythis-admin-notice-link">' . 'Don\'t show anymore' . '</a></span>',
            'later_link' => $two_week_review_temp,
            'int' => 7
        );

        $this->change_admin_notice_conveythis( $notices );
    }
}

// Creating the widget
class ConveyThisWidget extends WP_Widget {

    function __construct() {
        parent::__construct(

        // Base ID of your widget
            'ConveyThis',

            // Widget name will appear in UI
            __('ConveyThis', 'conveythis-translate'),

            // Widget description
            array( 'description' => __( 'ConveyThis language switcher', 'conveythis-translate' ), )
        );
    }

    // Creating widget front-end
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];

        // This is where you run the code and display the output
        echo do_shortcode("[conveythis_switcher]");
        echo $args['after_widget'];
    }

    // Widget Backend
    public function form( $instance ) {
        $title = !empty($instance['title']) ?  filter_var($instance['title'], FILTER_SANITIZE_STRING) : '';
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo  esc_attr($this->get_field_id( 'title' )); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo  esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo  esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo  esc_attr( $title ); ?>" />
        </p>
        <?php
    }

    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }

}


// Register and load the widget
function wp_register_widget() {
    register_widget( 'ConveyThisWidget' );
}
