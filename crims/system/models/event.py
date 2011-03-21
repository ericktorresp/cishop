from django.db import models
from system.models.drug import Drug
from filebrowser.fields import FileBrowseField
from django.utils.translation import ugettext_lazy as _

SECTIONS = (
   ('robbery', _('robbery')),
   ('drug', _('drug')),
   ('dock', _('dock')),
   ('building', _('building')),
   ('business', _('business')),
)
class Event(models.Model):
    title = models.CharField(_('title'), max_length=200)
    description = models.TextField(_('description'))
    section = models.CharField(_('section'), max_length=20, choices=SECTIONS)
    photo = FileBrowseField(_('photo'), max_length=200, directory="event/", format="Image", extensions=['.jpg', '.gif', '.png'])
    change = models.DecimalField(_('change'), decimal_places=4, max_digits=6)
    drug = models.ForeignKey(Drug, verbose_name=_('drug'), blank=True, null=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('event')
        verbose_name_plural = _('events')
        app_label = 'system'
        ordering = ['id', ]
