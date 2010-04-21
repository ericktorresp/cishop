from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Drug(models.Model):
    title = models.CharField(_('title'),max_length=100)
    price = models.IntegerField(_('price'))
    stock = models.IntegerField(_('stock'))
    stamina = models.SmallIntegerField(_('stamina'))
    spirit = models.SmallIntegerField(_('spirit'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    modified = models.DateTimeField(_('modified'), editable=False, auto_now=True)
    user_drug = models.ManyToManyField(User, through='UserDrug')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('Drug')
        verbose_name_plural = _('Drugs')
        app_label = 'system'
        ordering = ['id']

class UserDrug(models.Model):
    user = models.ForeignKey(User)
    drug = models.ForeignKey(Drug)
    units = models.IntegerField(_('units'))
    
    def __unicode__(self):
        return self.user.username + '\'s ' + self.drug.title
    
    class Meta:
        verbose_name = _('User\'s Drug')
        verbose_name_plural = _('User\'s Drugs')
        db_table = 'user_drug'
        app_label = 'system'
