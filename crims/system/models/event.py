from django.db import models
from system.models.drug import Drug
from filebrowser.fields import FileBrowseField
from django.utils.translation import ugettext_lazy, ugettext as _

SECTIONS = (
   ('robbery', _('Robbery')),
   ('drug', _('Drug')),
   ('dock', _('Dock')),
   ('building', _('Building')),
   ('business', _('Business')),
)
class Event(models.Model):
    title = models.CharField(max_length=200)
    description = models.TextField()
    section = models.CharField(max_length=20, choices=SECTIONS)
    photo = FileBrowseField(u'Photo', max_length=200, directory="event/", format="Image", extensions=['.jpg', '.gif', '.png'])
    change = models.DecimalField(decimal_places=4, max_digits=4)
    drug = models.ForeignKey(Drug, blank=True, null=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]
