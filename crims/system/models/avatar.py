from django.db import models
from filebrowser.fields import FileBrowseField

class Avatar(models.Model):
    filename = FileBrowseField(u'Image', max_length=200, directory="avatar/", format="Image", extensions=['.jpg', '.gif', '.png'])
    created = models.DateTimeField('Created at', editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return str(self.filename)
    
    class Meta:
        app_label = 'system'
