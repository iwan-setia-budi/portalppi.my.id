<div id="tab-rekap" class="tab-pane active">
  <div class="section-card">
    <h3>Rekap Kepatuhan per Bagian</h3>
    <div style="overflow-x:auto;">
      <table style="width:100%; min-width:680px; border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left; padding:10px; border-bottom:1px solid #dbe3ee;">Bagian</th>
            <th style="text-align:center; padding:10px; border-bottom:1px solid #dbe3ee;">Num (Ya)</th>
            <th style="text-align:center; padding:10px; border-bottom:1px solid #dbe3ee;">Denum</th>
            <th style="text-align:center; padding:10px; border-bottom:1px solid #dbe3ee;">%</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $rekapRows = [];
          $totalNum = 0;
          $totalDenum = 0;
          while ($row = mysqli_fetch_assoc($qRekapBagian)) {
            $num = (int) $row['num'];
            $den = (int) $row['denum'];
            $totalNum += $num;
            $totalDenum += $den;
            $rekapRows[] = $row;
            $persen = $den > 0 ? round(($num / $den) * 100, 2) : 0;
            echo '<tr>';
            echo '<td style="padding:10px; border-bottom:1px solid #eef2f7;">' . htmlspecialchars($row['kode_bagian']) . '</td>';
            echo '<td style="padding:10px; border-bottom:1px solid #eef2f7; text-align:center;">' . $num . '</td>';
            echo '<td style="padding:10px; border-bottom:1px solid #eef2f7; text-align:center;">' . $den . '</td>';
            echo '<td style="padding:10px; border-bottom:1px solid #eef2f7; text-align:center;">' . $persen . '%</td>';
            echo '</tr>';
          }
          ?>
        </tbody>
        <tfoot>
          <tr>
            <td style="padding:10px; font-weight:700;">Total</td>
            <td style="padding:10px; text-align:center; font-weight:700;"><?= $totalNum ?></td>
            <td style="padding:10px; text-align:center; font-weight:700;"><?= $totalDenum ?></td>
            <td style="padding:10px; text-align:center; font-weight:700;"><?= $totalDenum > 0 ? round(($totalNum / $totalDenum) * 100, 2) : 0 ?>%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
