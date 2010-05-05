from django.db import models
from system.models import UserBusiness
from game.models import Gang
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _


CHAT_TYPE = (
    ('square', _('square')),
    ('fight', _('fight')),
    ('prison', _('prison')),
    ('rip', _('rip')),
)
class Chat(models.Model):
    sender = models.ForeignKey(User, verbose_name=_('user'))
    gang = models.ForeignKey(Gang, verbose_name=_('gang'), blank=True, null=True)
    userbusiness = models.ForeignKey(UserBusiness, verbose_name=_('business'), blank=True, null=True)
    type = models.CharField(_('type'), max_length=10, choices=CHAT_TYPE, default='square', blank=True, null=True)
    content = models.CharField(_('body'), max_length=255)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.content
    
    class Meta:
        app_label = 'game'
        verbose_name = _('chat')
        verbose_name_plural = _('chats')
