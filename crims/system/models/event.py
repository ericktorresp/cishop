from django.db import models
from filebrowser.fields import FileBrowseField

SECTIONS = (
   ('', ''),
   ('robbery', 'Robbery'),
   ('drug', 'Drug'),
   ('dock', 'Dock'),
)
class Event(models.Model):
    title = models.CharField(max_length=200)
    description = models.CharField(max_length=255)
    section = models.CharField(max_length=20, choices=SECTIONS)
    photo = FileBrowseField(u'Photo', max_length=200, directory="event/", format="Image", extensions=['.jpg', '.gif', '.png'])
    change = models.DecimalField(decimal_places=4, max_digits=4)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
