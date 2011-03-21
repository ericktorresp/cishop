# Django settings for crims project.

DEBUG = True
TEMPLATE_DEBUG = DEBUG

# Coodr
X = (-400, 400)
Y = (-200, 200)

ADMINS = (
    # ('Your Name', 'your_email@domain.com'),
)

MANAGERS = ADMINS

DATABASE_ENGINE = 'mysql'           # 'postgresql_psycopg2', 'postgresql', 'mysql', 'sqlite3' or 'oracle'.
DATABASE_NAME = 'crims'             # Or path to database file if using sqlite3.
DATABASE_USER = 'root'             # Not used with sqlite3.
DATABASE_PASSWORD = '2908262'         # Not used with sqlite3.
DATABASE_HOST = 'localhost'             # Set to empty string for localhost. Not used with sqlite3.
DATABASE_PORT = '3306'             # Set to empty string for default. Not used with sqlite3.
DATABASE_OPTIONS = {"init_command": "SET storage_engine=INNODB"}
DATABASE_WAIT_TIMEOUT = 28800
# Local time zone for this installation. Choices can be found here:
# http://en.wikipedia.org/wiki/List_of_tz_zones_by_name
# although not all choices may be available on all operating systems.
# If running in a Windows environment this must be set to the same as your
# system time zone.
TIME_ZONE = 'Asia/Shanghai'
ugettext = lambda s: s

APE_SERVER = 'http://ape.crims.info:6969/?'
APE_PASSWORD = 'testpasswd'

LANGUAGES = (
    ('zh', ugettext('Chinese')),
    ('en', ugettext('English')),
)
# Language code for this installation. All choices can be found here:
# http://www.i18nguy.com/unicode/language-identifiers.html
LANGUAGE_CODE = 'zh-cn'

SITE_ID = 1

# If you set this to False, Django will make some optimizations so as not
# to load the internationalization machinery.
USE_I18N = True

# Absolute path to the directory that holds media.
# Example: "/home/media/media.lawrence.com/"

MEDIA_ROOT = '/Users/darkmoon/Projects/python/crims/assets/'
#MEDIA_ROOT = 'E:/server/py-projects/crims/assets/'

# URL that handles the media served from MEDIA_ROOT. Make sure to use a
# trailing slash if there is a path component (optional in other cases).
# Examples: "http://media.lawrence.com", "http://example.com/media/"
MEDIA_URL = 'http://localhost:8888/assets/'

# URL prefix for admin media -- CSS, JavaScript and images. Make sure to use a
# trailing slash.
# Examples: "http://foo.com/media/", "/media/".
ADMIN_MEDIA_PREFIX = 'http://localhost:8888/assets/admin/'

# Make this unique, and don't share it with anybody.
SECRET_KEY = 'cnz26^8bv!*gj^u*5-cg*dcec7q^mqbdz##846w-fgl+w)b-hz'

# List of callables that know how to import templates from various sources.
TEMPLATE_LOADERS = (
    'django.template.loaders.app_directories.load_template_source',
    'django.template.loaders.filesystem.load_template_source',
#     'django.template.loaders.eggs.load_template_source',
)

MIDDLEWARE_CLASSES = (
    'django.contrib.csrf.middleware.CsrfMiddleware',
    'django.middleware.transaction.TransactionMiddleware',
    'django.middleware.cache.UpdateCacheMiddleware',
    'system.middleware.fakesessioncookiemiddleware.FakeSessionCookieMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.locale.LocaleMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.middleware.cache.FetchFromCacheMiddleware',
)

ROOT_URLCONF = 'crims.urls'

TEMPLATE_DIRS = (
    '/Users/darkmoon/Projects/python/crims/templates',
#    'E:/server/py-projects/crims/templates',
)
# Memcache
# CACHE_BACKEND = 'memcached://127.0.0.1:11211/'
#CACHE_MIDDLEWARE_SECONDS = 3
#CACHE_MIDDLEWARE_KEY_PREFIX = 'crims_'

# Registration
ACCOUNT_ACTIVATION_DAYS = 2
EMAIL_USE_TLS = True
EMAIL_HOST = 'smtp.gmail.com'
EMAIL_HOST_USER = 'kirinse@gmail.com'
EMAIL_HOST_PASSWORD = 'nice2days'
EMAIL_PORT = 587
DEFAULT_FROM_EMAIL = 'kirinse@gmail.com'
LOGIN_REDIRECT_URL = '/'
REGISTRATION_OPEN = True
FILEBROWSER_DIRECTORY = ''
# FileBrowser
FILEBROWSER_DEBUG = False
FILEBROWSER_MEDIA_ROOT = MEDIA_ROOT
FILEBROWSER_MEDIA_URL = MEDIA_URL
FILEBROWSER_URL_FILEBROWSER_MEDIA = MEDIA_URL + 'filebrowser/'
FILEBROWSER_PATH_FILEBROWSER_MEDIA = MEDIA_ROOT + 'filebrowser/'
FILEBROWSER_DIRECTORY = 'uploads/'
FILEBROWSER_SAVE_FULL_URL = False
#TinyMCE
TINYMCE_DEFAULT_CONFIG = {
    'theme': "advanced",
    'relative_urls':True,
    'plugins':"style,searchreplace,paste,visualchars,pagebreak,table,emotions,inlinepopups,media,advhr,advimage,advlink,fullscreen,layer,contextmenu,noneditable,nonbreaking,insertdatetime",
    'theme_advanced_buttons1':"newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
    'theme_advanced_buttons2':"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    'theme_advanced_buttons3':"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,media,advhr,|,fullscreen",
    'theme_advanced_buttons4':"insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
    'theme_advanced_toolbar_location':"top",
    'theme_advanced_toolbar_align':"left",
    'theme_advanced_statusbar_location':"bottom",
    'theme_advanced_resizing':True,
}

INSTALLED_APPS = (
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.sites',
    'notification',
    'registration',
    'grappelli',
    'filebrowser',
    'tinymce',
    'captcha',
    'system',
    'messages',
    'game',
    'friends',
    'account',
    'record',
    'casino',
#    'relationships',
    'django.contrib.admin',
)

AUTH_PROFILE_MODULE = 'account.UserProfile'
SESSION_EXPIRE_AT_BROWSER_CLOSE = True
SESSION_ENGINE = 'django.contrib.sessions.backends.cached_db'
SESSION_SAVE_EVERY_REQUEST = True
SESSION_COOKIE_NAME = 'crimsess'
