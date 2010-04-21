from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Hooker(models.Model):
    title = models.CharField(_('title'),max_length=100)
    photo = FileBrowseField(_('photo'), max_length=200, directory="hooker/", format="Image", extensions=['.jpg', '.gif', '.png'])
    price = models.IntegerField(_('price'))
    expend = models.IntegerField(_('expend'))
    visitprice = models.IntegerField(_('visit price'))
    sickprobability = models.DecimalField(_('sick probability'),decimal_places=4, max_digits=4)
    is_random = models.BooleanField(_('random'),default=False)
    stamina = models.IntegerField(_('stamina'))
    spirit = models.SmallIntegerField(_('spirit'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    updated = models.DateTimeField(_('updated'), editable=False, auto_now=True)
    user_hooker = models.ManyToManyField(User, through='UserHooker')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('Hooker')
        verbose_name_plural = _('Hookers')
        app_label = 'system'
        ordering = ['id', ]

class UserHooker(models.Model):
    user = models.ForeignKey(User)
    hooker = models.ForeignKey(Hooker)
    visitprice = models.SmallIntegerField(_('visit price'))
    expend = models.SmallIntegerField(_('expend'))
    income = models.IntegerField(_('income'))
    freetime = models.DateTimeField(_('freetime'),blank=True, null=True)
    
    def __unicode__(self):
        return self.user.username + '\'s ' + self.hooker.title
    
    class Meta:
        verbose_name = _('User\'s Hooker')
        verbose_name_plural = _('User\'s Hookers')
        db_table = 'user_hooker'
        app_label = 'system'
