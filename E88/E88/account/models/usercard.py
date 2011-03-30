from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

from home.models import Bank

class UserCard(models.Model):
    bank = models.ForeignKey(Bank)
    user = models.ForeignKey(User)
    alias = models.CharField(_("Alias"), max_length=20)
    account_name = models.CharField(_('Account name'), max_length=30)
    branch = models.CharField(_('Branch'), max_length=50)
    card_no = models.CharField(_('Card No'), max_length=30)
    add_time = models.DateTimeField(_('Add time'), auto_now_add=True)
    
    def __unicode__(self):
        return "%s %s %s" % (self.user.username, self.bank.name, self.card_no)
    
    class Meta:
        db_table = u'user_card'
        verbose_name = _('User\'s card')
        verbose_name_plural = _('User\'s cards')