from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
from django.conf import settings

from depositmethodaccount import DepositMethodAccount

"""
user deposit logs
"""

class DepositLog(models.Model):
    user = models.ForeignKey(User, verbose_name=_('user'), related_name='depositer')
    deposit_method = models.ForeignKey(DepositMethodAccount, verbose_name=_('receive account'))
    deposit_time = models.DateTimeField(_('deposit time'), auto_now_add=True)
    received = models.BooleanField(_('received'), default=False)
    
    class Meta:
        app_label = 'bank'
        db_table = u'deposit_log'
        verbose_name = _('deposit log')