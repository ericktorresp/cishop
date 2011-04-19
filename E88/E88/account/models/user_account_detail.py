from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _
from user_account_detail_type import UserAccountDetailType

class UserAccountDetail(models.Model):
    from_user = models.ForeignKey(User, verbose_name=_('from user'), null=True, blank=True, related_name='from_user')
    to_user = models.ForeignKey(User, verbose_name=_('to user'), null=True, blank=True, related_name='to_user')
    detail_type = models.ForeignKey(UserAccountDetailType, verbose_name=_('user account detail type'), related_name='detail_type')
    admin = models.ForeignKey(User, verbose_name=_('admin'), null=True, blank=True, related_name='admin_user')
    title = models.CharField(_('title'), max_length=30)
    description = models.CharField(_('description'), max_length=100)
    amount = models.DecimalField(_('amount'), max_digits=14, decimal_places=4)
    pre_balance = models.DecimalField(_('pre balance'), max_digits=14, decimal_places=4)
    post_balance = models.DecimalField(_('post balance'), max_digits=14, decimal_places=4)
    client_ip = models.IPAddressField(_('client ip'))
    proxy_ip = models.IPAddressField(_('proxy ip'))
    db_time = models.DateTimeField(_('db time'), auto_now = True)
    action_time = models.DateTimeField(_('action time'))
    
    def __unicode__(self):
        return self.title

    def save(self, *args, **kwargs):
        self.title = self.detail_type.name
        super(UserAccountDetail, self).save(*args, **kwargs)
    
    class Meta:
        app_label = 'account'
        db_table = u'user_account_detail'
        verbose_name = _('user account detail')
    

