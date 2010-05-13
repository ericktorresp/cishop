from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Respect(models.Model):
    user = models.ForeignKey(User, verbose_name=_('user'), related_name='daily_respect_user')
    respect = models.IntegerField(_('respect'))
    day = models.PositiveSmallIntegerField(_('day'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return _('daily respect for %s') % (self.user.username)

    class Meta:
        app_label = 'record'
        verbose_name = _('daily respect')
        verbose_name_plural = _('daily respects')
        unique_together = ('user', 'day')
