from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Bounty(models.Model):
    sponsor = models.ForeignKey(User, verbose_name=_('sponsor'), related_name='sponsor')
    target = models.ForeignKey(User, verbose_name=_('target'), related_name='target')
    credits = models.SmallIntegerField(_('credits'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    expired = models.DateTimeField(_('expired'))
    completed = models.BooleanField(_('completed'), default=False)
    
    def __unicode__(self):
        return _('%(sponsor)s put bounty on %(target)s, price is %(price)s credits.') % {'sponsor': self.sponsor.username, 'target': self.target.username, 'price':self.credits}

    class Meta:
        app_label = 'game'
        verbose_name = _('Bounty')
        verbose_name_plural = _('Bounties')
