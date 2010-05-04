from django.db import models
from filebrowser.fields import FileBrowseField
from system.models.drug import Drug
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Building(models.Model):
    title = models.CharField(_('title'), max_length=100)
    output = models.IntegerField(_('output'))
    expend = models.IntegerField(_('expend'))
    price = models.IntegerField(_('price'))
    photo = FileBrowseField(_('photo'), max_length=200, directory="building/", format="Image", extensions=['.jpg', '.gif', '.png'])
    drug = models.ForeignKey(Drug, verbose_name=_('drug'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    user_building = models.ManyToManyField(User, through='UserBuilding')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('building')
        verbose_name_plural = _('buildings')
        app_label = 'system'
        ordering = ['id']

class UserBuilding(models.Model):
    user = models.ForeignKey(User, verbose_name=_('user'))
    building = models.ForeignKey(Building, verbose_name=_('building'))
    title = models.CharField(_('title'), max_length=200, blank=True, null=True)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    modified = models.DateTimeField(_('modified'), editable=False, auto_now=True)
    units = models.IntegerField(_('units'))
    outputs = models.IntegerField(_('outputs'))
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('user\'s building')
        verbose_name_plural = _('user\'s buildings')
        db_table = 'user_building'
        app_label = 'system'
