from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Drug(models.Model):
    title = models.CharField(_('title'), max_length=100)
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
        verbose_name = _('drug')
        verbose_name_plural = _('drugs')
        app_label = 'system'
        ordering = ['id']

class UserDrug(models.Model):
    user = models.ForeignKey(User, verbose_name=_('user'))
    drug = models.ForeignKey(Drug, verbose_name=_('drug'))
    units = models.IntegerField(_('units'))
    
    def __unicode__(self):
        return self.drug.title
    
    class Meta:
        verbose_name = _('user\'s drug')
        verbose_name_plural = _('user\'s drugs')
        db_table = 'user_drug'
        app_label = 'system'
        unique_together = ('user', 'drug')
