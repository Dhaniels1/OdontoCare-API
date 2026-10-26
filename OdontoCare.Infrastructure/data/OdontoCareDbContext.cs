
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

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

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
