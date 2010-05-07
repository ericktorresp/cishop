from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _
from django.conf import settings
from system.models import Province, Character
from game.thumbs import ImageWithThumbsField

GENDER = (('m', _('male')), ('f', _('female')), ('u', _('unknown')))
STEPS = (
    (1, _('info accept')),
    (2, _('benefit')),
    (3, _('done')),
)

class UserProfile(models.Model):
    user = models.ForeignKey(User, unique=True)
    birthday = models.DateField(_('birthday'), blank=True, null=True)
    gender = models.CharField(_('gender'), max_length=1, choices=GENDER, default='u')
    presentation = models.TextField(_('presentation'))
    gb_open = models.BooleanField(_('guestbook open?'), default=True)
    language = models.CharField(_('language'), choices=settings.LANGUAGES, max_length=10)
    visitors = models.IntegerField(_('visitors'), default=0)
    province = models.ForeignKey(Province, verbose_name=_('province'))
    avatar = ImageWithThumbsField(_('avatar'), upload_to='uploads/user_avatar', sizes=((100, 100),))
#    models.CharField(_('avatar'), max_length=100, blank=True, null=True)
    character = models.ForeignKey(Character, verbose_name=_('character'))
    rabbit_mode = models.BooleanField(_('rabbit mode?'), default=False)
    step = models.SmallIntegerField(_('step'), choices=STEPS)
    
    def __unicode__(self):
        return self.user.username

    def delete(self):
        if self.user:
            return False
        super(UserProfile, self).delete()
    
    class Meta:
        app_label = 'account'
        verbose_name = _('profile')
        verbose_name_plural = _('profiles')
        db_table = 'user_profile'

    class Admin:
        list_display = ('gender',)
