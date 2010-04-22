from django.db import models
from filebrowser.fields import FileBrowseField
from django.utils.translation import ugettext_lazy as _

class Character(models.Model):
    title = models.CharField(_('title'),max_length=100)
    avatar = FileBrowseField(_('avatar'), max_length=200, directory="character/", format="Image", extensions=['.jpg', '.gif', '.png'])
    photo = FileBrowseField(_('photo'), max_length=200, directory="character/", format="Image", extensions=['.jpg', '.gif', '.png'])
    intelligence = models.IntegerField(_('Intelligence'), max_length=6)
    strength = models.IntegerField(_('Strength'), max_length=6)
    charisma = models.IntegerField(_('Charisma'), max_length=6)
    tolerance = models.IntegerField(_('Tolerance'), max_length=6)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('Character')
        verbose_name_plural = _('Characters')
        app_label = 'system'
        ordering = ['id']
