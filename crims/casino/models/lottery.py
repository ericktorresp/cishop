from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Lottery(models.Model):
    user = models.ForeignKey(User, verbose_name = _('user'))
    created = models.DateTimeField(_('created'), editable = False, auto_now_add = True)

    def __unicode__(self):
        return self.user.username

    class Meta:
        app_label = 'casino'
        verbose_name = _('lottery')
        verbose_name_plural = _('lotteries')
