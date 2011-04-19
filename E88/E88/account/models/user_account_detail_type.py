from django.db import models
from django.utils.translation import ugettext_lazy as _

class UserAccountDetailType(models.Model):
    name = models.CharField(_('name'), max_length=30)
    operation = models.CharField(_('operation'), max_length=1, choices=(('0','-'),('1','+')), default=0)
    description = models.CharField(_('description'), max_length=255)
    
    def __unicode__(self):
        return self.name
    
    class Meta:
        db_table = u'user_account_detail_type'
        app_label = 'account'
        verbose_name = _('user account detail type')