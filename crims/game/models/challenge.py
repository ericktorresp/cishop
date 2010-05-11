from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

import datetime

class Challenge(models.Model):
    user = models.ForeignKey(User, verbose_name=_('user'), related_name='challenge_user')
    victim = models.ForeignKey(User, verbose_name=_('victim'), related_name='challenge_victim')
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    expired = models.DateTimeField(_('expired'), default=datetime.datetime.now() + datetime.timedelta(hours=6))

    def __unicode__(self):
        return _('%(user)s challenged %(victim)s') % (self.user.username, self.victim.username)
    
    class Meta:
        app_label = 'game'
        verbose_name = _('challenge')
        verbose_name_plural = _('challenges')
