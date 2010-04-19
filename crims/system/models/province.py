from django.db import models
from filebrowser.fields import FileBrowseField

class Province(models.Model):
    title = models.CharField(max_length=100)
    icon = FileBrowseField(u'Photo', max_length=200, directory="province/", format="Image", extensions=['.jpg', '.gif', '.png'])

    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
