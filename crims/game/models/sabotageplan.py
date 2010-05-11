from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

from system.models import Sabotage
from game.models.gang import ACTION_STATUS

class SabotagePlan(models.Model):
    sabotage = models.ForeignKey(Sabotage, verbose_name=_('sabotage'))
    user = models.ForeignKey(User, verbose_name=_('user'), related_name='sabotage_user')
    victim = models.ForeignKey(User, verbose_name=_('victim'), related_name='sabotage_victim')
    status = models.CharField(_('status'), choices=ACTION_STATUS, max_length=10, default='planning')
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.sabotage.title
    
    class Meta:
        app_label = 'game'
        verbose_name = _('sabotage plan')
        verbose_name_plural = _('sabotage plans')
