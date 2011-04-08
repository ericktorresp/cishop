from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

class Cellphone(models.Model):
    number = models.CharField(_('number'), max_length=11)
    adder = models.ForeignKey(User, verbose_name=_('add user'), editable=False, related_name='cellphone_adder')
    add_time = models.DateTimeField(_('add time'), auto_now_add=True)
    verifier = models.ForeignKey(User, verbose_name=_('verify user'), null=True, blank=True, editable=False, related_name='cellphone_verifier')
    verify_time = models.DateTimeField(_('verify time'), blank=True, null=True, editable=False)
    
    def __unicode__(self):
        return self.number
    
    class Meta:
        app_label = 'bank'
        db_table = u'bank_cellphone'
        verbose_name = _('cellphone')
        permissions = (
            ("can_verify", "Can verify"),
        )