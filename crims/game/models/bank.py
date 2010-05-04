from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _
from django.forms import ValidationError

class Bank(models.Model):
    user = models.OneToOneField(User, verbose_name=_('user'), related_name='user')
    amount = models.IntegerField(_('amount'), default=0)
    created = models.DateTimeField(_('created'), auto_now_add=True)
    modified = models.DateTimeField(_('modified'), auto_now=True)

    def __unicode__(self):
        return _('%(user)s\'s bank account') % {'user':self.user.username}
    
    def delete(self):
        if self.user:
            return False
        super(Bank, self).delete()
    
    class Meta:
        app_label = 'game'
        verbose_name = _('Bank')
        verbose_name_plural = _('Banks')
        ordering = ['id']
