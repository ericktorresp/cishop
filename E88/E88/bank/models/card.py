from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
from django.conf import settings

from home.models import Country, Province
from bank import Bank

class Card(models.Model):
    card_no = models.CharField(_('Card No.'), max_length=87)
    bank = models.ForeignKey(Bank, verbose_name=_('Bank'))
    alias = models.CharField(_('Alias'), max_length=30)
    currency = models.CharField(_('Currency'), max_length=5)
    account_name = models.CharField(_('Account Name'), max_length=20)
    init_balance = models.DecimalField(_('Init Balance'), max_digits=14, decimal_places=4)
    login_pwd = models.CharField(_('Login Password'), max_length=30)
    transaction_pwd = models.CharField(_('Transaction Password'), max_length=30)
    country = models.ForeignKey(Country, verbose_name = _('Country'))
    province = models.ForeignKey(Province, verbose_name = _('Province'))
    discriminator = models.CharField(_('discriminator'), max_length=10, choices=(('withdraw',_('Withdraw')),('internal',_('Internal'))))
    enabled = models.BooleanField(_('Enabled'), default=False)
    adder = models.ForeignKey(User, verbose_name=_('add user'), editable=False, related_name='card_adder')
    add_time = models.DateTimeField(auto_now_add=True,verbose_name=_('Add Time'))
    verify_time = models.DateTimeField(_('Verify Time'),blank=True, null=True, editable=False)
    verifier = models.ForeignKey(User, verbose_name=_('Verifier'), null=True, blank=True, editable=False, related_name='card_verifier')
        
    def __unicode__(self):
        return _("%s: %s") % (self.bank.name, self.card_no)
    
    class Meta:
        app_label = 'bank'
        db_table = u'card'
        verbose_name = _('Card')
        verbose_name_plural = _('Cards')
        permissions = (
            ("can_verify", "Can verify"),
        )
