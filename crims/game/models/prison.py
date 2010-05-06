from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Prison(models.Model):
    prisoner = models.ForeignKey(User, verbose_name=_('prisoner'))
    created = models.DateTimeField(_('created'), auto_now_add=True)
    expired = models.DateTimeField(_('expired'))
    escaped = models.BooleanField(_('escaped'), default=False)

    def __unicode__(self):
        return self.prisoner.username
    
    class Meta:
        app_label = 'game'
        verbose_name = _('prison')
        verbose_name_plural = _('prison')
