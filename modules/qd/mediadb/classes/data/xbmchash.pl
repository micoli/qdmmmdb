#!/usr/bin/perl

sub xbmcHash {

  my( $hashInput ) = shift;
  $hashInput = lc $hashInput;
  my $m_crc = 0xFFFFFFFF;
  
  for my $byte( unpack 'C*', $hashInput ) {
    $m_crc = $m_crc ^ ( $byte << 24 );
    for (my $rep = 0; $rep < 8; $rep++) {
      if (( $m_crc & 0x80000000) == 0x80000000) {
        $m_crc = ($m_crc << 1) ^ 0x04C11DB7;
      } else {
        $m_crc = ($m_crc << 1); 
      }
    }
  }
  return $m_crc;
}

my $dataToHash = $ARGV[0];

printf "%08x\n", xbmcHash($dataToHash);
