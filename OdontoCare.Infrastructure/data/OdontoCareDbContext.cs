
using Microsoft.EntityFrameworkCore;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Infrastructure.Data;

public class OdontoCareDbContext : DbContext
{
    public OdontoCareDbContext(DbContextOptions<OdontoCareDbContext> options)
        : base(options)
    {
    }

    public DbSet<Paciente> Pacientes => Set<Paciente>();
    public DbSet<Consulta> Consultas => Set<Consulta>();
    public DbSet<Usuario> Usuarios => Set<Usuario>();
    public DbSet<Procedimento> Procedimentos => Set<Procedimento>();
    public DbSet<Material> Materiais => Set<Material>();
    public DbSet<Equipamento> Equipamentos => Set<Equipamento>();
    public DbSet<MovimentacaoEstoque> MovimentacoesEstoque => Set<MovimentacaoEstoque>();

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        modelBuilder.Entity<Procedimento>(e =>
        {
            e.ToTable("procedimento"); e.HasKey(x => x.IdProcedimento);
            e.Property(x => x.IdProcedimento).HasColumnName("idProcedimento").ValueGeneratedOnAdd();
            e.Property(x => x.NomeProcedimento).HasColumnName("nomeProcedimento");
            e.Property(x => x.Descricao).HasColumnName("descricao");
            e.Property(x => x.ValorProcedimento).HasColumnName("valorProcedimento").HasPrecision(10, 2);
        });
        modelBuilder.Entity<Usuario>(e =>
        {
            e.ToTable("usuario"); e.HasKey(x => x.IdUsuario);
            e.Property(x => x.IdUsuario).HasColumnName("idUsuario").ValueGeneratedOnAdd();
            e.Property(x => x.Nome).HasColumnName("nome").HasMaxLength(200).IsRequired();
            e.Property(x => x.Email).HasColumnName("email").HasMaxLength(200);
            e.Property(x => x.Login).HasColumnName("login").HasMaxLength(50).IsRequired();
            e.Property(x => x.Senha).HasColumnName("senha").HasMaxLength(500).IsRequired();
            e.Property(x => x.TipoUsuario).HasColumnName("tipoUsuario").HasMaxLength(50);
            e.HasIndex(x => x.Login).IsUnique();
        });
        modelBuilder.Entity<Material>(e =>
        {
            e.ToTable("material"); e.HasKey(x => x.IdMaterial);
            e.Property(x => x.IdMaterial).HasColumnName("idMaterial").ValueGeneratedOnAdd();
            e.Property(x => x.NomeMaterial).HasColumnName("nomeMaterial").HasMaxLength(200).IsRequired();
            e.Property(x => x.Descricao).HasColumnName("descricao").HasColumnType("text");
            e.Property(x => x.Quantidade).HasColumnName("quantidade");
            e.Property(x => x.EstoqueMinimo).HasColumnName("estoqueMinimo");
            e.Property(x => x.Unidade).HasColumnName("unidade").HasMaxLength(30);
        });
        modelBuilder.Entity<Equipamento>(e =>
        {
            e.ToTable("equipamento"); e.HasKey(x => x.IdEquipamento);
            e.Property(x => x.IdEquipamento).HasColumnName("idEquipamento").ValueGeneratedOnAdd();
            e.Property(x => x.NomeEquipamento).HasColumnName("nomeEquipamento").HasMaxLength(200).IsRequired();
            e.Property(x => x.Descricao).HasColumnName("descricao").HasColumnType("text");
            e.Property(x => x.StatusEquipamento).HasColumnName("statusEquipamento").HasMaxLength(50);
        });
        modelBuilder.Entity<MovimentacaoEstoque>(e =>
        {
            e.ToTable("movimentacao_estoque"); e.HasKey(x => x.IdMovimentacao);
            e.Property(x => x.IdMovimentacao).HasColumnName("idMovimentacao").ValueGeneratedOnAdd();
            e.Property(x => x.IdMaterial).HasColumnName("idMaterial");
            e.Property(x => x.Tipo).HasColumnName("tipo").HasMaxLength(10).IsRequired();
            e.Property(x => x.Quantidade).HasColumnName("quantidade");
            e.Property(x => x.DataUtc).HasColumnName("dataUtc").HasColumnType("datetime(6)");
            e.Property(x => x.IdUsuario).HasColumnName("idUsuario");
            e.HasOne<Material>().WithMany(x => x.Movimentacoes)
                .HasForeignKey(x => x.IdMaterial).OnDelete(DeleteBehavior.Restrict);
        });

        modelBuilder.Entity<Paciente>(entity =>
        {
            entity.ToTable("paciente");

            entity.HasKey(p => p.IdPaciente);

            entity.Property(p => p.IdPaciente)
                .HasColumnName("idPaciente")
                .ValueGeneratedOnAdd();

            entity.Property(p => p.Nome)
                .HasColumnName("nome")
                .HasMaxLength(200)
                .IsRequired();

            entity.Property(p => p.Cpf)
                .HasColumnName("cpf")
                .HasMaxLength(14)
                .IsRequired();

            entity.HasIndex(p => p.Cpf)
                .IsUnique();

            entity.Property(p => p.Telefone)
                .HasColumnName("telefone")
                .HasMaxLength(20);

            entity.Property(p => p.Email)
                .HasColumnName("email")
                .HasMaxLength(200);

            entity.Property(p => p.Cep)
                .HasColumnName("cep")
                .HasMaxLength(10);

            entity.Property(p => p.Logradouro)
                .HasColumnName("logradouro")
                .HasMaxLength(100);

            entity.Property(p => p.Numero)
                .HasColumnName("numero")
                .HasMaxLength(10);

            entity.Property(p => p.Bairro)
                .HasColumnName("bairro")
                .HasMaxLength(60);

            entity.Property(p => p.Cidade)
                .HasColumnName("cidade")
                .HasMaxLength(60);

            entity.Property(p => p.Estado)
                .HasColumnName("estado")
                .HasMaxLength(2);

            entity.Property(p => p.DataNascimento)
                .HasColumnName("dataNascimento")
                .HasColumnType("date");

            entity.Property(p => p.HistoricoOdontologico)
                .HasColumnName("historicoOdontologico")
                .HasColumnType("text");

            entity.Property(p => p.Alergias)
                .HasColumnName("alergias")
                .HasColumnType("text");

            entity.Property(p => p.Exames)
                .HasColumnName("exames")
                .HasColumnType("text");

            entity.Property(p => p.TratamentosAnteriores)
                .HasColumnName("tratamentosAnteriores")
                .HasColumnType("text");

            entity.Property(p => p.ProntuarioClinicoDigital)
                .HasColumnName("prontuarioClinicoDigital")
                .HasColumnType("text");
        });

        modelBuilder.Entity<Consulta>(entity =>
        {
            entity.ToTable("consulta");

            entity.HasKey(c => c.IdConsulta);

            entity.Property(c => c.IdConsulta)
                .HasColumnName("idConsulta")
                .ValueGeneratedOnAdd();

            entity.Property(c => c.DataConsulta)
                .HasColumnName("dataConsulta")
                .HasColumnType("date")
                .IsRequired();

            entity.Property(c => c.HorarioConsulta)
                .HasColumnName("horarioConsulta")
                .HasColumnType("time")
                .IsRequired();

            entity.Property(c => c.StatusConsulta)
                .HasColumnName("statusConsulta")
                .HasMaxLength(30);

            entity.Property(c => c.Observacoes)
                .HasColumnName("observacoes")
                .HasColumnType("text");

            entity.Property(c => c.IdUsuario)
                .HasColumnName("idUsuario")
                .IsRequired();

            entity.Property(c => c.IdPaciente)
                .HasColumnName("idPaciente")
                .IsRequired();

            entity.Property(c => c.IdProcedimento)
                .HasColumnName("idProcedimento")
                .IsRequired();
        });
    }
}
