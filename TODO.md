Re-implementer land-funksjoner
==
```
if( $arrangement->getType() == 'land' ) {
    foreach( UKMMonstring_sitemeta_storage() as $key ) {
        UKMmonstring::addViewData(
            $key,
            get_site_option( 'UKMFvideresending_'.$key.'_'.$arrangement->getSesong() )
        );
    }
}
```
