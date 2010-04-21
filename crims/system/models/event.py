from django.db import models
from system.models.drug import Drug
from filebrowser.fields import FileBrowseField
from django.utils.translation import ugettext_lazy as _

SECTIONS = (
   ('robbery', _('Robbery')),
   ('drug', _('Drug')),
   ('dock', _('Dock')),
   ('building', _('Building')),
   ('business', _('Business')),
)
class Event(models.Model):
    title = models.CharField(_('title'),max_length=200)
    description = models.TextField(_('description'))
    section = models.CharField(_('section'),max_length=20, choices=SECTIONS)
    photo = FileBrowseField(_('photo'), max_length=200, directory="event/", format="Image", extensions=['.jpg', '.gif', '.png'])
    change = models.DecimalField(_('change'),decimal_places=4, max_digits=4)
    drug = models.ForeignKey(Drug, blank=True, null=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('Event')
        verbose_name_plural = _('Events')
        app_label = 'system'
        ordering = ['id', ]
