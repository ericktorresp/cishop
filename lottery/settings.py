# Django settings for mysite project.

DEBUG = True
TEMPLATE_DEBUG = DEBUG
DEFAULT_CHARSET = 'utf-8'
ADMINS = (
    # ('Your Name', 'your_email@domain.com'),
)

MANAGERS = ADMINS

DATABASE_ENGINE = 'mysql_pool'           # 'postgresql_psycopg2', 'postgresql', 'mysql', 'sqlite3' or 'oracle'.
DATABASE_NAME = 'blog'             # Or path to database file if using sqlite3.
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

LANGUAGES = (
    ('zh', ugettext('Chinese')),
    ('en', ugettext('English')),
)

# Language code for this installation. All choices can be found here:
# http://www.i18nguy.com/unicode/language-identifiers.html

LANGUAGE_CODE = 'en-US'

SITE_ID = 1

# If you set this to False, Django will make some optimizations so as not
# to load the internationalization machinery.
USE_I18N = True

# Absolute path to the directory that holds media.
# Example: "/home/media/media.lawrence.com/"
MEDIA_ROOT = '/Users/darkmoon/pys/mysite/assets/'

# URL that handles the media served from MEDIA_ROOT. Make sure to use a
# trailing slash if there is a path component (optional in other cases).
# Examples: "http://media.lawrence.com", "http://example.com/media/"
MEDIA_URL = 'http://myhoney.cn:8000/assets/'

# URL prefix for admin media -- CSS, JavaScript and images. Make sure to use a
# trailing slash.
# Examples: "http://foo.com/media/", "/media/".
ADMIN_MEDIA_PREFIX = 'http://myhoney.cn:8000/assets/admin/'

# Make this unique, and don't share it with anybody.
SECRET_KEY = 'fzvjnx44s24aq%_kn&x(bys42l9^(0=^w7&5jgtpsjtpvj_ke='

# List of callables that know how to import templates from various sources.
TEMPLATE_LOADERS = (
    'django.template.loaders.app_directories.load_template_source',
    'django.template.loaders.filesystem.load_template_source',
#     'django.template.loaders.eggs.load_template_source',
)

MIDDLEWARE_CLASSES = (
    'django.contrib.csrf.middleware.CsrfMiddleware',
    'django.middleware.transaction.TransactionMiddleware',
#    'django.middleware.cache.UpdateCacheMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.locale.LocaleMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
#    'django.middleware.cache.FetchFromCacheMiddleware'
)
# 
import re
DISALLOWED_USER_AGENTS = (
    re.compile(r'^OmniExplorer_Bot'),
    re.compile(r'^Googlebot')
)

ROOT_URLCONF = 'mysite.urls'

TEMPLATE_DIRS = (
    # Put strings here, like "/home/html/django_templates" or "C:/www/django/templates".
    # Always use forward slashes, even on Windows.
    # Don't forget to use absolute paths, not relative paths.
     'E:/AppServ/pys/mysite/templates'
)
# Memcache
#CACHE_BACKEND = 'memcached://127.0.0.1:11211/'
#CACHE_MIDDLEWARE_SECONDS = 3
#CACHE_MIDDLEWARE_KEY_PREFIX = 'mysite_'

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
FILEBROWSER_DEBUG = True 
FILEBROWSER_MEDIA_ROOT = MEDIA_ROOT 
FILEBROWSER_MEDIA_URL = MEDIA_URL 
FILEBROWSER_URL_FILEBROWSER_MEDIA = MEDIA_URL + 'filebrowser/' 
FILEBROWSER_PATH_FILEBROWSER_MEDIA = MEDIA_ROOT + 'filebrowser/' 
FILEBROWSER_DIRECTORY = 'uploads/'
#TinyMCE
TINYMCE_DEFAULT_CONFIG = {
                          'theme': "advanced",
                          'relative_urls':True,
                          'theme_advanced_toolbar_align':'left',
                          'theme_advanced_toolbar_location':"top",
                          'static_domain':'myhoney.cn',
                          'plugins':'table,contextmenu,paste',
                          }
INSTALLED_APPS = (
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.sites',
#    'polls',
    'channels',
    'lotteries',
    'helps',
    'notices',
    'accounts',
    'records',
    'registration',
    'grappelli',
    'filebrowser',
    'tinymce',
    'django.contrib.admin'
)
TEMPLATE_CONTEXT_PROCESSORS = (
    "django.core.context_processors.auth",
    "django.core.context_processors.request",
)
