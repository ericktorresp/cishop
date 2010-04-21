from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Armor(models.Model):
    title = models.CharField(_('title'), max_length=100)
    tolerance = models.IntegerField(_('tolerance'), max_length=6)
    price = models.IntegerField(_('price'))
    photo = FileBrowseField(_('photo'), max_length=200, directory="armor/", format="Image", extensions=['.jpg', '.gif', '.png'])
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    user_armor = models.ManyToManyField(User, through='UserArmor')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('Armor')
        verbose_name_plural = _('Armors')
        app_label = 'system'
        ordering = ['id', ]

class UserArmor(models.Model):
    user = models.ForeignKey(User)
    armor = models.ForeignKey(Armor)
    actived = models.BooleanField(_('actived'), default=False)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.user.username + '\'s' + self.armor.title
    
    class Meta:
        verbose_name = _('User\'s Armor')
        verbose_name_plural = _('User\'s Armors')
        db_table = 'user_armor'
        app_label = 'system'
