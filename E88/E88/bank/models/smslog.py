from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
from django.conf import settings

from depositmethod import DepositMethod
from depositmethodaccount import DepositMethodAccount
from cellphone import Cellphone

class SmsLog(models.Model):
    sender = models.CharField(_('sender number'), max_length=20, db_index=True)
    receiver = models.ForeignKey(Cellphone, to_field="number", db_column=u'receive_number', verbose_name=_('number'))
    content = models.CharField(_('sms content'), max_length=500)
    receive_time = models.DateTimeField(_('receive time'), auto_now_add=True)

    def __unicode__(self):
        return self.receiver
    
    class Meta:
        app_label = 'bank'
        db_table = u'deposit_sms_log'
        verbose_name = _('SMS log')