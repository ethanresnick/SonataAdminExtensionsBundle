SonataAdminBundle is indispensable for getting an admin UI up in a reasonable amount of time. But it can still be
a bit tedious, and this bundle aims to fix that.

First, it makes defining separate Admin Classes unnecessary and instead stores the admin configuration information as
annotations directly on the entity classes. This makes it easier to keep your admin ui and data model in sync, because
they're both defined in the same place.

Its syntax also aims to be much more concise than the one used Sonata's Admin Classes. That syntax requires a lot of
repetition, as the same properties must be redefined in all four admin views (often with the same order/settings), and
does little to encourage reuse, because it's hard for one Admin class to extend another and make use of its existing
declarations.

So this bundle starts with the idea of defining basic settings for each property that are then used in all admin views,
unless overridden by settings for the specific admin view. This is shorter than defining all settings for each view.
So any property can have one of these "basic settings" annotations, and one or more "view specific" annotations.

Also, in this bundle, all annotations are inherited by, and overrideable in, subclasses. And there's a simple interface
to write little "plugin" functions for the class that generates your admin class from the annotations, thereby allowing
you to skip much of the boilerplate and instead generate it programmatically.