from django.db import models
from filebrowser.fields import FileBrowseField
from django.utils.translation import ugettext_lazy as _

class Character(models.Model):
    title = models.CharField(_('title'), max_length=100)
    avatar = FileBrowseField(_('avatar'), max_length=200, directory="character/", format="Image", extensions=['.jpg', '.gif', '.png'])
    photo = FileBrowseField(_('photo'), max_length=200, directory="character/", format="Image", extensions=['.jpg', '.gif', '.png'])
    intelligence = models.IntegerField(_('intelligence'), max_length=6)
    strength = models.IntegerField(_('strength'), max_length=6)
    charisma = models.IntegerField(_('charisma'), max_length=6)
    tolerance = models.IntegerField(_('tolerance'), max_length=6)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('character')
        verbose_name_plural = _('characters')
        app_label = 'system'
        ordering = ['id']
